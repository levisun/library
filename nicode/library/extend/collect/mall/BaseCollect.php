<?php
/**
 *
 */
class BaseCollect extends Model
{

    protected $cacheDir = '';

    protected $mall = array();

    public function _initialize()
    {
        $this->cacheDir = EXT_ROOT_PATH . 'runtime' . DS . 'cache_collect' . DS;
    }

    /**
     * 顶部价格
     * @access public
     * @param
     * @return array
     */
    public function topPrice($mall_type, $search)
    {
        if (is_array($mall_type)) {
            $top_price = array();
            foreach ($mall_type as $key => $value) {
                $top_price[] = array(
                    'name'  => $key,
                    'price' => $this->topPrice($value['name'], $search),
                    'url'   => $value['url'] . '&u=&n=&s=' . urlencode($search),
                );
            }
            // halt($top_price);
            return $top_price;
        } else {
            $rule = '/[^\x{4e00}-\x{9fa5}a-zA-Z0-9\s\_\-\(\)\[\]\{\}\|\?\/\!\@\#\$\%\^\&\+\=\:\;\"\'\<\>\,\.\，\。\《\》\\\\]+/u';
            $search = preg_replace($rule, '', $search);
            $search = mb_substr($search, 0, 20, 'utf-8');

            $method = $mall_type . 'Page';
            if (method_exists($this, $method)) {
                $params = array(
                    'page'      => 1,
                    'cate_name' => '',
                    'search'    => $search,
                    'all'       => true,
                );
                $result = $this->$method($params);

                return isset($result[1]['price']) ? $result[1]['price'] : false;
            } else {
                return false;
            }

        }
    }

    /**
     * 商城商品详情
     * @access public
     * @param  string $mall_type 商城名称
     * @param  string $url
     * @return array
     */
    public function detail($mall_type, $url)
    {
        $method = $mall_type . 'Detail';
        if (method_exists($this, $method)) {
            $result = $this->$method($url);
            $result['top_price'] = $this->topPrice($this->mall_type, $result['title']);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 商城列表页
     * @access public
     * @param  string $mall_type 商城名
     * @param  int    $id        分类ID
     * @param  int    $page      分页
     * @param  string $search
     * @return array
     */
    public function page($mall_type, $id, $page, $search)
    {
        $result = $this->getProt($id);
        // 商城筛选属性
        $prop = $result;
        // 当前分类名 多级:分割
        $cate_name = $result['cate_data']['name'];
        $cate_name = str_replace(':', ' ', $cate_name);

        // 筛选属性
        if ($search) {
            $search = str_replace(':', ' ', $search);
        }
        // 全站搜索
        $all = false;
        if (false !== strpos($search, 'ALL_')) {
            $search = str_replace('ALL_', '', $search);
            $all = true;    // true 不拼接分类栏目名
        }

        $method = $mall_type . 'Page';
        if (method_exists($this, $method)) {
            $params = array(
                'id'        => $id,
                'prop'      => $prop,
                'page'      => $page,
                'cate_name' => $cate_name,
                'search'    => $search,
                'all'       => $all,
            );
            $result = $this->$method($params);

            $top_key = $all ? $search : $cate_name . $search;

            $result = array(
                'item'      => $result,
                'prop'      => $all ? array() : $prop['prop'],
                'brand'     => $prop['brand'],
            );

            // 前1页商品获得顶部比价价格
            if ($page <= 1) {
                $result['top_price'] = $this->topPrice($this->mall_type, $top_key);
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * 商城分类
     * 基于天猫商城
     * @access public
     * @param  int    $pid
     * @return array
     */
    public function getCat($pid = 0)
    {
        $map = 'parent_id=' . $pid . ' AND id<>3 AND type<>2';
        $result =
        $this->table('collect_cat')
        ->field('id, category_id, name, type, icon, url, prop')
        ->where($map)
        ->order('sort ASC')
        ->all();

        foreach ($result as $key => $value) {
            if ($value['url']) {
                $result[$key]['url'] = urlencode($value['url']);
            }

            if ($pid) {
                $child = $this->getCat($value['category_id']);
                if ($child) {
                    $result[$key]['child'] = $child;
                }
            }

        }

        return $result;
    }

    /**
     * 商城筛选属性
     * 基于天猫
     * @access protected
     * @param  int    $id 分类ID
     * @param
     * @return array
     */
    protected function getProt($id)
    {
        // 当前分类信息
        $map = array('id' => $id);
        $cate_data =
        $this->table('collect_cat')
        ->field('id, category_id, parent_id, name, type, icon, url, prop')
        ->where($map)
        ->find();

        $prop = $this->getProp($id);
        $brand = $this->getBrand($id);

        if (empty($prop) && empty($brand)) {
            // 当前类的筛选属性
            $result = $this->snoopy('https:' . $cate_data['prop']);
            $result = json_decode($result, true);

            $prop = array();
            foreach ($result['prop_list'] as $key => $value) {
                $prop[$key] = array(
                    'name' => $value['name'],
                );
                if (isset($value['value_list'])) {
                    foreach ($value['value_list'] as $k => $val) {
                        $prop[$key]['child'][] = array(
                            'name' => $val['name'],
                            // 'id'   => $value['id'] . ':' . $val['value'],
                        );
                    }
                }
            }
            $this->createProp($id, $prop);

            // 当前类的筛选品牌
            $brand = array();
            foreach ($result['brand_list'] as $key => $value) {
                if ($value['logo']) {
                    $brand[$key] = array(
                        'name' => $value['brand_name'],
                        'logo' => $value['logo'],
                    );
                }
            }

            $this->createBrand($id, $brand);
        }

        // 当前分类父级信息
        $map = array('c.category_id' => $cate_data['parent_id']);
        $parent =
        $this->table('ts_coll as c')
        ->join('INNER JOIN ' . $this->getTable('ts_coll') . ' as pc ON(pc.category_id=c.parent_id)')
        ->field('pc.name')
        ->where($map)
        ->find();

        if (in_array($parent['name'], array('为您推荐', '国际大牌'))) {
            $cate_data['name'] = $cate_data['name'];
        } else {
            $cate_data['name'] = $parent['name'] . ':' . $cate_data['name'];
        }

        return array(
            'prop'      => $prop,
            'brand'     => $brand,
            'cate_data' => $cate_data,
        );
    }

    /**
     * 获得分类筛选属性
     * @access protected
     * @param  int       $id  分类ID
     * @return array
     */
    protected function getBrand($id)
    {
        $map = 'category_id=' . $id;
        $result =
        $this->table('collect_brand')
        ->field('id, category_id, name, logo')
        ->where($map)
        ->order('id ASC')
        ->all();

        return $result;
    }

    /**
     * 录入分类筛选属性
     * @access protected
     * @param  int       $id   分类ID
     * @param  array     $data 筛选属性
     * @return viod
     */
    protected function createBrand($id, $data)
    {
        foreach ($data as $key => $value) {
            $save_data = array(
                'category_id' => $id,
                'name'        => $value['name'],
                'logo'        => $value['logo'],
            );
            $this->table('collect_brand')
            ->save($save_data);
        }
    }

    /**
     * 获得分类筛选属性
     * @access protected
     * @param  int       $id  分类ID
     * @param  int       $pid 父级属性ID
     * @return array
     */
    protected function getProp($id, $pid = 0)
    {
        $map = 'parent_id=' . $pid . ' AND category_id=' . $id;
        $result =
        $this->table('collect_prop')
        ->field('id, category_id, name')
        ->where($map)
        ->order('id ASC')
        ->all();

        foreach ($result as $key => $value) {
            $child = $this->getProp($id, $value['id']);
            if ($child) {
                $result[$key]['child'] = $child;
            }

        }
        return $result;
    }

    /**
     * 录入分类筛选属性
     * @access protected
     * @param  int       $id   分类ID
     * @param  array     $data 筛选属性
     * @param  int       $pid  父级属性ID
     * @return viod
     */
    protected function createProp($id, $data, $pid = 0)
    {
        foreach ($data as $key => $value) {
            $save_data = array(
                'category_id' => $id,
                'parent_id'   => $pid,
                'name'        => $value['name'],
            );
            $this->table('collect_prop')
            ->save($save_data);
            if (isset($value['child'])) {
                $this->createProp($id, $value['child'], $this->last_insert_id);
            }
        }
    }

    /**
     * 采集数据
     * @access protected
     * @param  string  $url
     * @param  array   $params  请求参数
     * @param  string  $charset 数据编码
     * @return array
     */
    protected function snoopy($url, $params = array(), $charset = '', $headers = array())
    {
        $snoopy = new Snoopy;
        $agent = array(
            'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',
            'Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.23 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.23 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 6 Build/LYZ28E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.23 Mobile Safari/537.36',

            'Mozilla/5.0 (BB10; Touch) AppleWebKit/537.1+ (KHTML, like Gecko) Version/10.0.0.1337 Mobile Safari/537.1+',
            'Mozilla/5.0 (MeeGo; NokiaN9) AppleWebKit/534.13 (KHTML, like Gecko) NokiaBrowser/8.5.0 Mobile Safari/534.13',
            'Mozilla/5.0 (BlackBerry; U; BlackBerry 9900; en-US) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.0.0.187 Mobile Safari/534.11+',
            'Mozilla/5.0 (iPad; CPU OS 4_3_2 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8H7 Safari/6533.18.5',
            'Mozilla/5.0 (iPad; CPU OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3',
            'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5376e Safari/8536.25',
            'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_2 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8H7 Safari/6533.18.5',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5376e Safari/8536.25',
            'Mozilla/5.0 (Linux; Android 4.1.2; Nexus 7 Build/JZ054K) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Safari/535.19',
            'Mozilla/5.0 (Linux; Android 4.0.4; Galaxy Nexus Build/IMM76B) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.133 Mobile Safari/535.19',
        );
        $key = array_rand($agent, 1);
        $snoopy->agent = $agent[$key];
        $snoopy->headers = $headers;

        if (!$result = $this->collCache($url . json_encode($params))) {
            if (!empty($params)) {
                $snoopy->submit($url, $params);
            } else {
                $snoopy->fetch($url);
            }

            $result = $snoopy->results;

            if ($charset != '' && strtoupper($charset) == 'UTF-8') {
                $result = iconv('GB2312', 'UTF-8//IGNORE', $result);
            }

            $this->collCache($url . json_encode($params), $result);
        }

        return $result;
    }

    /**
     * 消除过期缓存
     * @access protected
     * @param
     * @return boolean
     */
    protected function removeCollCache()
    {
        if (rand(1, 10) != 10) {
            return false;
        }
        $list = File::get($this->cacheDir);
        if (empty($list)) {
            return false;
        }

        $days = strtotime('-7 days');
        foreach ($list as $key => $value) {
            if ($value['time'] >= $days) {
                unset($list[$key]);
            }
        }

        if (empty($list)) {
            return false;
        }

        $count = count($list) >= 20 ? 20 : count($list);
        $rand = array_rand($list, $count);

        // $days = strtotime('-7 days');
        $total = 0;
        foreach ($list as $key => $value) {
            if ($total >= $count) {
                break;
            }

            if (in_array($key, $rand)) {
                // 删除过期缓存
                File::delete($dir . $value['name']);
                $total++;
            }
        }
    }

    /**
     * 缓存
     * @access protected
     * @param  string    $name
     * @param  array     $data
     * @param  int       $expire 4个小时
     * @return mixed
     */
    protected function collCache($name, $data = '', $expire = 14400)
    {
        $this->removeCollCache();

        $name = __CLASS__ . $name;
        $file_name = md5($name) . '.php';

        if (is_file($this->cacheDir . $file_name)) {
            $result = include($this->cacheDir . $file_name);
            if ($result['time'] >= time()) {
                return htmlspecialchars_decode($result['data']);
            }
        }

        if (!empty($data)) {
            // 过虑回车与空格
            $data = preg_replace('/[\s]+/si', ' ', $data);

            if (false !== strpos($data, '400 The plain HTTP request was sent to HTTPS port')) {
                return false;
            }

            $array = array(
                'name' => $name,
                'data' => htmlspecialchars($data),
                'time' => time() + $expire,
                );
            $array = '<?php return ' . var_export($array, true) . '; ?>';

            file_put_contents($this->cacheDir . $file_name, $array, true);
        }

        return false;
    }
}
