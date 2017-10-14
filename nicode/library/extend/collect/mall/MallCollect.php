<?php

class MallCollect extends BaseCollect
{
    protected $mall_type = array(
        // '天猫' => array('name' => 'tmall', 'url' => 'coll.php?method=list&t=tmall'),
        '淘宝' => array('name' => 'taobao', 'url' => 'coll.php?method=list&t=taobao'),
        '京东' => array('name' => 'jd', 'url' => 'coll.php?method=list&t=jd'),
        '苏宁' => array('name' => 'suning', 'url' => 'coll.php?method=list&t=suning'),
        '一号店' => array('name' => 'yhd', 'url' => 'coll.php?method=list&t=yhd'),
        '当当' => array('name' => 'dangdang', 'url' => 'coll.php?method=list&t=dangdang'),
        '聚美优品' => array('name' => 'jumei', 'url' => 'coll.php?method=list&t=jumei'),
    );

    public function gomeDetail($url)
    {
        $url = 'http:' . urldecode($url);
        $result = $this->snoopy($url);

        // preg_match('/(<h1 class="title_text">)(.*?)(<\/h1>)/si', $result, $matches);
        // $detail['title'] = trim(trim($matches[2]), "\r\n");

        // preg_match_all('/(<li class="swiper-slide">[\s]<a href="javascript:;"><img src=")(.*?)(")/si', $result, $matches);

        // foreach ($matches[2] as $key => $value) {
        //     $detail['images'][] = 'http:' . $value;
        // }

        preg_match('/(var gData[\s]= {)(.*?)(};)/si', $result, $matches);
        $json = '{' . $matches[2] . '}';
        $json = json_decode($json, true);

        $detail['title'] = $json['goods']['name'];
        foreach ($json['goods']['goods_img'] as $key => $value) {
            $detail['images'][] = 'http:' . $value . '_400.jpg';
        }

        $get  = 'http://item.m.gome.com.cn/product/stock';
        $get .= '?goodsNo=' . $json['get']['goodsNo'];
        $get .= '&skuID=' . $json['get']['skuID'];
        $get .= '&shopId=' . $json['get']['shopId'];
        $get .= '&shopType=0&provinceId=11000000&cityId=11010000&districtId=11010200&townId=110102002&modelId=&stid=&mid=&isFirst=Y&isPresent=0&ajax=1';
        $get .= '&_=' . time();
        $result = $this->snoopy($get, array(), '', array('X-Requested-With' => 'XMLHttpRequest'));

        halt($get);


        // http://item.m.gome.com.cn/product/stock?goodsNo=A0006318008&skuID=pop8010544016&shopId=80014795&shopType=0&provinceId=11000000&cityId=11010000&districtId=11010200&townId=110102002&modelId=&stid=&mid=&isFirst=Y&isPresent=0&ajax=1&_=1507336930841
        // http://item.m.gome.com.cn/product/stock?goodsNo=A0006200517&skuID=pop8009877270&shopId=80013173&shopType=0&provinceId=11000000&cityId=11010000&districtId=11010200&townId=110102002&modelId=&stid=&mid=&isFirst=Y&isPresent=0&ajax=1&_=1506741749373
        // http://item.m.gome.com.cn/product/detail?goodsNo=A0006200517&skuID=pop8009877270&ajax=1&_=1506743091795

        halt($json);
    }
    public function gomePage($params)
    {
        $url = 'http://m.gome.com.cn/category.html?from=1&scat=3&key_word=';
        if ($params['all']) {
            $url .= $params['search'];
        } else {
            $url .= $params['cate_name'] . $params['search'];
        }
        $url .= '&page=' . $params['page'];

        $result = $this->snoopy($url);

        preg_match_all('/(<a onClick="urlClick\(this\)" href=")(.*?)(" name=")/si', $result, $matches);
        $url = $matches[2];

        preg_match_all('/(<span class="gd_img"><img src=")(.*?)(" onerror=")/si', $result, $matches);
        $img = $matches[2];

        preg_match_all('/(<strong class="title ellipsis_two">)(.*?)(<\/strong>)/si', $result, $matches);
        $title = $matches[2];

        preg_match_all('/(<span class="price">)(.*?)(<\/span>)/si', $result, $matches);
        $price = $matches[2];

        $item = array();
        foreach ($url as $key => $value) {
            $item[] = array(
                'image' => 'http:' . $img[$key],
                'name'  => strip_tags(trim($title[$key])),
                'url'   => urlencode($value),
                'price' => str_replace('&yen;', '', strip_tags(trim($price[$key]))),
                );
        }

        return $item;
    }

    /**
     * 唯品会
     * 商品详情
     * @access public
     * @param  string $url
     * @return array
     */
    public function vipDetail($url)
    {
        $url = '';
    }

    /**
     * 唯品会
     * 商品列表
     * @access public
     * @param  array $params
     * @return array
     */
    public function vipPage($params)
    {
        $url = 'https://m.vip.com/server.html?rpc&method=SearchRpc.getSearchList&f=';
        $form = array(
            'id' => time() . rand(111, 999),
            'jsonrpc' => '2.0',
            'method' => 'SearchRpc.getSearchList',
            'params' => array(
                'brand_ids' => '',
                'brand_store_sn' => '',
                'category_id_1_5_show' => '',
                'category_id_1_show' => '',
                'category_id_2_show' => '',
                'category_id_3_show' => '',
                'channel_id' => '',
                'ep' => 20,
                'keyword' => '',
                'np' => $params['page'],
                'page' => 'searchlist.html',
                'price_end' => '',
                'price_start' => '',
                'props' => '',
                'query' => '',
                'size_name' => '',
                'sort' => 0,
            ),
            'method' => $params['page'],
            );
        if ($params['all']) {
            $form['params']['keyword'] = $params['search'];
            $form['params']['query'] = 'q=' . urlencode($params['search']) . '&channel_id=';
        } else {
            $form['params']['keyword'] = $params['cate_name'] . $params['search'];
            $form['params']['query'] = 'q=' . urlencode($params['cate_name'] . $params['search']) . '&channel_id=';
        }

        halt($form);

        $result = $this->snoopy($url, $form);

        halt($result);
    }

    /**
     * 聚美优品
     * 商品详情
     * @access public
     * @param  string $url
     * @return array
     */
    public function jumeiDetail($url)
    {
        $url = 'http:' . urldecode($url);

        $q_url = 'http://h5.jumei.com/product/ajaxStaticDetail?type=jumei_mall&item_id=';
        $q_url .= substr($url, 59);
        $result = $this->snoopy($q_url);
        $json = json_decode($result, true);

        $detail = array(
            'url' => $url,
            'title' => $json['data']['name'],
        );

        foreach ($json['data']['image_url_set']['single_many'] as $key => $value) {
            $detail['images'][] = $value['480'];
        }

        $detail['desc'] = $json['data']['description_info']['description'];

        $prop = array();
        foreach ($json['data']['properties'] as $key => $value) {
            $prop[] = array(
                $value['name'] => $value['value'],
            );
        }
        $detail['prop'] = array(
            '规格参数' => $prop
        );

        $q_url = 'http://h5.jumei.com/product/ajaxDynamicDetail?type=jumei_mall&item_id=';
        $q_url .= substr($url, 59);
        $result = $this->snoopy($q_url);
        $json = json_decode($result, true);
        $detail['price'] = $json['data']['result']['size'][0]['jumei_price'];


        // halt($detail);
        return $detail;
    }
    /**
     * 聚美优品
     * 商品列表
     * @access public
     * @param  array $params
     * @return array
     */
    public function jumeiPage($params)
    {
        $url = 'http://h5.jumei.com/search/index?search=';
        $url .= $params['all'] ? urlencode($params['search']) : urlencode($params['cate_name'] . $params['search']);
        $url .= '&page=' . $params['page'] . '&ajax=get';

        $result = $this->snoopy($url);
        $json = json_decode($result, true);
        $item = array();
        foreach ($json['data']['item_list'] as $key => $value) {
            $item[] = array(
                'image' => $value['image_url_set']['single']['320'],
                'name'  => $value['name'],
                'url'   => urlencode('//h5.jumei.com/product/detail?type=' . $value['type'] . '&item_id=' . $value['item_id']),
                'price' => $value['jumei_price'],
                );
        }

        return $item;
    }

    /**
     * 当当
     * 商品详情
     * @access public
     * @param  string $url
     * @return array
     */
    public function dangdangDetail($url)
    {
        $url = 'http:' . urldecode($url);
        $result = $this->snoopy($url);

        $detail['url'] = $url;

        preg_match('/(<article>)(.*?)(<\/article>)/si', $result, $matches);
        $detail['title'] = $matches[2];

        preg_match('/(<span id="main_price">)(.*?)(<\/span>)/si', $result, $matches);
        $detail['price'] = $matches[2];

        preg_match('/(<ul class="top-slider" style="width:500%;">)(.*?)(<\/ul>)/si', $result, $matches);
        $images = $matches[2];
        preg_match('/(<img src=")(.*?)(")/si', $images, $m);
        $detail['images'][] = $m[2];
        preg_match_all('/(imgsrc=")(.*?)(")/si', $images, $m);
        foreach ($m[2] as $key => $value) {
            $detail['images'][] = $value;
        }

        preg_match('/(<a dd_name="顶部详情" href=")(.*?)(")/si', $result, $matches);
        $desc = $this->snoopy($matches[2]);
        preg_match('/(<section data-content-name="详情" class="area j_area">)(.*?)(<\/section>)/si', $desc, $matches);
        $detail['desc'] = $matches[2];

        preg_match('/(<section data-content-name="规格参数" class="area j_area">)(.*?)(<\/section>)/si', $desc, $matches);

        preg_match_all('/(<em>)(.*?)(<\/em>)/si', $matches[2], $m);
        preg_match_all('/(<i>)(.*?)(<\/i>)/si', $matches[2], $i);

        $prop = array();
        foreach ($m[2] as $key => $value) {
            $prop[] = array(
                $value => $i[2][$key]
            );
        }
        $detail['prop'] = array(
            '规格参数' => $prop
        );

        return $detail;
    }

    /**
     * 当当
     * 商品列表
     * @access public
     * @param  array $params
     * @return array
     */
    public function dangdangPage($params)
    {
        $url = 'http://search.m.dangdang.com/search_ajax.php?act=get_product_flow_search';
        $url .= '&t=' . time() . '&page=' . $params['page'] . '&keyword=';
        $url .= $params['all'] ? urlencode($params['search']) : urlencode($params['cate_name'] . $params['search']);
        $result = $this->snoopy($url);
        $json = json_decode($result, true);

        $item = array();
        foreach ($json['products'] as $key => $value) {
            $item[] = array(
                'image' => $value['image_url'],
                'name'  => $value['name'],
                'url'   => urlencode(substr($value['product_url'], 5)),
                'price' => $value['price'],
                );
        }

        return $item;
    }

    /**
     * 1号店
     * 商品详情
     * @access public
     * @param  string $url
     * @return array
     */
    public function yhdDetail($url)
    {
        $url = 'http:' . urldecode($url);
        $result = $this->snoopy($url);

        $detail['url'] = $url;

        preg_match('/(<h2 class="pd_product-title" id="pd_product-title">)(.*?)(<\/h2>)/si', $result, $matches);
        $detail['title'] = $matches[2];

        preg_match('/(<span class="pd_product-price-num">)(.*?)(<\/span>)/si', $result, $matches);
        $detail['price'] = $matches[2];

        preg_match_all('/(data-src=")(.*?)(")/si', $result, $matches);
        $detail['images'] = $matches[2];

        preg_match('/(detailparams={)(.*?)(};)/si', $result, $matches);
        preg_match('/(h5proSignature:")(.*?)(",)/si', $matches[2], $m);
        $h5proSignature = $m[2];
        preg_match('/(productId:)(.*?)(,)/si', $matches[2], $m);
        $productId = $m[2];
        preg_match('/(pmId:)(.*?)(,)/si', $matches[2], $m);
        $pmId = $m[2];

        $url = 'http://item.m.yhd.com/item/ajaxProductDesc.do?callback=jsonp12&productId=' . $productId . '&pmId=' . $pmId . '&uid=' . $h5proSignature;
        $result = $this->snoopy($url);
        $json = substr($result, 8);
        $json = substr($json, 0, -1);
        $json = json_decode($json, true);
        $detail['desc'] = $json['data'][0]['tabDetail'];

        $prop = array();
        foreach ($json['data'][1]['productParamsVoList'][0]['descParamVoList'] as $key => $value) {
            $prop[] = array(
                $value['attributeName'] => $value['attributeValue']
            );
        }
        $detail['prop'] = array(
            '规格参数' => $prop
        );

        return $detail;
    }

    /**
     * 1号店
     * 商品列表
     * @access public
     * @param  array $params
     * @return array
     */
    public function yhdPage($params)
    {
        $url = 'http://search.m.yhd.com/search/k';
        $url .= $params['all'] ? urlencode($params['search']) : urlencode($params['cate_name'] . $params['search']);
        $url .= '/p' .$params['page'] . '-s1-si1-t1?req.ajaxFlag=1';
        $result = $this->snoopy($url);

        preg_match_all('/(<a href=")(.*?)(" class="item">)/si', $result, $matches);
        $url = $matches[2];

        preg_match_all('/(<div class="pic_box">)(.*?)(<\/div>)/si', $result, $matches);
        preg_match_all('/(src="|original=")(.*?)(")/si', implode(' ', $matches[2]), $matches);
        $img = array();
        foreach ($matches[2] as $key => $value) {
            if (strpos($value, '.svg') === false) {
                $img[] = $value;
            }
        }

        preg_match_all('/(<div class="title_box">)(.*?)(<\/div>)/si', $result, $matches);
        $title = $matches[2];

        preg_match_all('/(<small>¥<\/small><i>)(.*?)(<\/i>)/si', $result, $matches);
        $price = $matches[2];

        $item = array();
        foreach ($url as $key => $value) {
            $title[$key] = str_replace('<span class="self_sell">自营</span>', '', $title[$key]);
            $item[] = array(
                'image' => 'http:' . $img[$key],
                'name'  => trim($title[$key]),
                'url'   => urlencode($value),
                'price' => $price[$key],
                );
        }

        return $item;
    }

    /**
     * 苏宁
     * 商品详情
     * @access public
     * @param  string $url
     * @return array
     */
    public function suningDetail($url)
    {
        $url = 'https:' . urldecode($url);
        $result = $this->snoopy($url);

        $detail['url'] = $url;

        preg_match('/("productName": ")(.*?)(",)/', $result, $matches);
        $detail['title'] = $matches[2];

        preg_match_all('/(<img ori-src=")(.*?)(")/', $result, $matches);
        foreach ($matches[2] as $key => $value) {
            $detail['images'][] = 'https:' . $value . '400x400.jpg';
        }
        preg_match_all('/(image">[\s]<img data-src=")(.*?)(")/', $result, $matches);
        foreach ($matches[2] as $key => $value) {
            $detail['images'][] = 'https:' . $value . '400x400.jpg';
        }

        // 请求所需参数
        preg_match('/("passPartNumber": ")(.*?)(",)/', $result, $matches);
        $passPartNumber = $matches[2];

        preg_match('/("supplierCode": ")(.*?)(",)/', $result, $matches);
        $supplierCode = $matches[2];

        preg_match('/("categoryCode_mdm": ")(.*?)(",)/', $result, $matches);
        $categoryCode_mdm = $matches[2];

        preg_match('/("brandCode": ")(.*?)(",)/', $result, $matches);
        $brandCode = $matches[2];

        preg_match('/(<div class="desc-spec-param desc-spec-item">)(.*?)(<div class="tab-desc desc-sale">)/', $result, $matches);
        preg_match_all('/(<div>)(.*?)(<\/div>)/', $matches[2], $matches);
        $prop = array();
        foreach ($matches[2] as $key => $value) {
            $k = $key * 2;
            $kp = $k + 1;
            if (isset($matches[2][$k])) {
                $prop[] = array(
                    $matches[2][$k] => $matches[2][$kp]
                );
            }
        }
        $detail['prop'] = array(
            '规格参数' => $prop
        );

        $url = 'https://pas.suning.com/nssnitemsale_' . $passPartNumber . '_' . $supplierCode . '_250_029_0290199_0_5__999_1_____1000257.html?callback=wapData';
        $price = file_get_contents($url);
        $price = substr($price, 8);
        $price = substr($price, 0, -2);
        $json = json_decode($price, true);
        $detail['price'] = isset($json['data']['price']['saleInfo'][0]['promotionPrice']) ?
        $json['data']['price']['saleInfo'][0]['promotionPrice'] :
        $json['data']['price']['saleInfo'][0]['netPrice'];

        $url = 'http://product.m.suning.com/pds-web/ajax/selfUniqueInfoJsonp_' . $passPartNumber . '_' . $supplierCode . '_' . $categoryCode_mdm . '_' . $brandCode . '_itemUnique.html?callback=itemUnique';
        $desc = $this->snoopy($url);
        $desc = substr($desc, 11);
        $desc = substr($desc, 0, -1);
        $json = json_decode($desc, true);
        $detail['desc'] = $json['itemDetail']['phoneDetail'];
        $detail['desc'] = str_replace('src2', 'src', $detail['desc']);

        return $detail;
    }

    /**
     * 苏宁
     * 商品列表
     * @access public
     * @param  array $params
     * @return array
     */
    public function suningPage($params)
    {
        $url = 'https://search.suning.com/emall/mobile/wap/clientSearch.jsonp?cityId=010&channel=&ps=10&st=0&set=5&cf=&iv=-1&ci=&ct=-1&channelId=WAP&sp=&sg=&sc=&prune=&operate=0&isAnalysised=0&istongma=1&v=99999999&callback=success_jsonpCallback';

        $params['page']--;
        $url .= '&cp=' . $params['page'] . '&keyword=';
        $url .= $params['all'] ? urlencode($params['search']) : urlencode($params['cate_name'] . $params['search']);

        $result = $this->snoopy($url);
        $result = substr($result, 22);
        $result = substr($result, 0, -2);

        $json = json_decode($result, true);

        $item = array();
        foreach ($json['goods'] as $key => $value) {
            $item[] = array(
                'image' => 'https://image3.suning.cn/uimg/b2c/newcatentries/' . $value['salesCode'] . '-000000000' . $value['catentryId'] . '_1_400x400.jpg',
                'name'  => $value['catentdesc'],
                'url'   => urlencode('//m.suning.com/product/' . $value['salesCode'] . '/' . $value['catentryId'] . '.html'),
                'price' => $value['price'],
                );
        }

        return $item;
    }

    /**
     * 京东
     * 商品详情
     * @access public
     * @param  string $url
     * @return array
     */
    public function jdDetail($url)
    {
        // https://item.m.jd.com/ware/detail.json?wareId=3356012
        $id = substr($url, 24);
        $id = substr($id, 0, -5);
        $_url = 'https://item.m.jd.com/ware/detail.json?wareId=' . $id;
        $result = $this->snoopy($_url);
        $json = json_decode($result, true);
        $json['ware']['wi']['code'] = json_decode($json['ware']['wi']['code'], true);

        $detail = array(
                'id'     => $id,
                'title'  => $json['ware']['wname'],
                // 'price'  => $json['ware']['jdPrice'],
                'desc'   => $json['wdis'],
                // 'prop'   => $json['ware']['wi']['code'],
                'url'    => 'https:' . $url,
            );
        if (isset($json['ware']['popWareDetailWebViewMap']['cssContent'])) {
            $detail['desc'] = $json['ware']['popWareDetailWebViewMap']['cssContent'] . $detail['desc'];
        }
        foreach ($json['ware']['images'] as $key => $value) {
            $detail['images'][] = $value['bigpath'];
        }
        foreach ($json['ware']['wi']['code'] as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $ke => $val) {
                    if (is_array($val)) {
                        foreach ($val as $k => $v) {
                            $detail['prop'][$ke][] = $v;
                        }
                    } else {
                        $detail['prop'][' '][][$ke] = $val;
                    }
                }
            }
        }

        $result = $this->snoopy($detail['url']);
        preg_match('/(<span class="plus-jd-price-text" id="specJdPrice"> )(.*?)( <\/span>)/', $result, $matches);
        $detail['price'] = $matches[2];

        return $detail;
    }

    /**
     * 京东
     * 商品列表
     * @access public
     * @param  array $params
     * @return array
     */
    public function jdPage($params)
    {
        $url = 'https://so.m.jd.com/ware/searchList.action';
        $form = array(
            '_format_' => 'json',
            'sort' => '',
            'page' => $params['page'],
            );
        if ($params['all']) {
            $form['keyword'] = $params['search'];
        } else {
            $form['keyword'] = $params['cate_name'] . $params['search'];
        }

        $result = $this->snoopy($url, $form);
        $json = json_decode($result, true);
        $json = json_decode($json['value'], true);
        $item = array();
        foreach ($json['wareList']['wareList'] as $key => $value) {
            $item[] = array(
                'image' => $value['imageurl'],
                'name'  => $value['wname'],
                'url'   => urlencode('//item.m.jd.com/product/' . $value['wareId'] . '.html'),
                'price' => $value['jdPrice'],
                );
        }

        return $item;
    }

    /**
     * 淘宝
     * 商品详情
     * @access public
     * @param  string $url
     * @return array
     */
    public function taobaoDetail($url)
    {
        if (strpos($url, '.tmall.com')) {
            return $this->tmallDetail($url);
        } else {
            $url = str_replace('{', '%7B', $url);
            $url = str_replace('}', '%7D', $url);
            $url = str_replace('\"', '%22', $url);
            $url = str_replace(':', '%3A', $url);
            $url = str_replace(',', '%2C', $url);
            $url = 'http:' . $url;
            $result = $this->snoopy($url);
            $json = json_decode($result, true);
            $json['data']['mockData'] = json_decode($json['data']['mockData'], true);

            $detail = array(
                'id'     => $json['data']['item']['itemId'],
                'title'  => $json['data']['item']['title'],
                'images' => $json['data']['item']['images'],
                'price'  => $json['data']['mockData']['price']['price']['priceText'],
                'desc'   => 'http://api.m.taobao.com/h5/mtop.taobao.detail.getdesc/6.0/?data=%7B%22id%22%3A%22' . $json['data']['item']['itemId'] . '%22%2C%22type%22%3A%220%22%2C%22f%22%3A%22TB14Unck8cHL1JjSZJi8qwKcpla%22%7D',
                'prop'   => $json['data']['props']['groupProps'][0],
            );

            $desc = $this->snoopy($detail['desc']);
            $desc = json_decode($desc, true);
            $desc = $desc['data']['wdescContent']['pages'];
            // $desc = str_replace('<img size=750x660>', '<img src="', $desc);
            $desc = preg_replace('/(<img size=[a-zA-Z0-9]+>)/si', '<img src="', $desc);
            $desc = str_replace('</img>', '" />', $desc);

            $desc = str_replace('<txt>', '<p>', $desc);
            $desc = str_replace('</txt>', '</p>', $desc);

            $detail['desc'] = implode('', $desc);
        }

        $detail['url'] = 'http://h5.m.taobao.com/awp/core/detail.htm?id=' . $detail['id'];

        return $detail;
    }

    /**
     * 淘宝
     * 商品列表
     * @access public
     * @param  array $params
     * @return array
     */
    public function taobaoPage($params)
    {
        $url = '//s.m.taobao.com/search?event_submit_do_new_search_auction=1&_input_charset=utf-8&topSearch=1&atype=b&searchfrom=1&action=home%3Aredirect_app_action&from=1&sst=1&n=20&buying=buyitnow&m=api4h5&abtest=18&wlsort=18&page=' . $params['page'] . '&q=';
        $url .= $params['all'] ? urlencode($params['search']) : urlencode($params['cate_name'] . $params['search']);

        $result = $this->snoopy('https:' . $url);
        $result = json_decode($result, true);
        $item = array();
        foreach ($result['listItem'] as $key => $value) {
            if ($value['isP4p'] == 'false') {
                $item[$key] = array(
                    'mall_type' => 'taobao',
                    'image'     => $value['img2'],
                    'name'      => $value['title'],
                    // 'url'       => urlencode($value['url']),
                    'price'     => $value['priceWap'],
                );

                if (strpos($value['url'], '.tmall.com')) {
                    $item[$key]['url'] = urlencode($value['url']);
                } else {
                    $value['url'] = '//h5.m.taobao.com/awp/core/detail.htm?id=' . $value['item_id'];
                    $value['url'] = '//h5api.m.taobao.com/h5/mtop.taobao.detail.getdetail/6.0/?data={"exParams":"{"id":"' . $value['item_id'] . '"}","itemNumId":"' . $value['item_id'] . '"}';
                    $item[$key]['url'] = urlencode($value['url']);
                }
            }
        }

        return $item;
    }

    /**
     * 天猫
     * 商品详情
     * @access public
     * @param  string $url
     * @return array
     */
    public function tmallDetail($url)
    {
        $url    = 'https:' . urldecode($url);
        $result = $this->snoopy($url, array(), 'UTF-8');

        preg_match('/(var _DATA_Detail = {)(.*?)(};)/si', $result, $matches);

        $json = json_decode('{' . $matches[2] . '}', true);

        $detail = array(
            'id'     => $json['item']['itemId'],
            'title'  => $json['item']['title'],
            'images' => $json['item']['images'],
            'price'  => $json['mock']['price']['price']['priceText'],
            'desc'   => $json['jumpUrl']['apis']['httpsDescUrl'],
            'prop'   => $json['props']['groupProps'][0],
        );
        // 采集详情
        $desc = $this->snoopy('https:' . $detail['desc'], array(), 'UTF-8');
        $desc = str_replace('var desc=', '', $desc);
        $desc = str_replace('width="790"', '', $desc);
        // $desc = str_replace('width="730"', '', $desc);
        $desc = str_replace('width: 790.0px;', '', $desc);
        // $desc = str_replace('width: 730.0px;', '', $desc);

        $detail['desc'] = trim($desc, "'");
        $detail['url'] = $url;

        return $detail;
    }

    /**
     * 天猫
     * 商品列表
     * @access public
     * @param  array $params
     * @return array
     */
    public function tmallPage($params)
    {
        if ($params['search']) {
            // 全站搜索
            $url = '//list.tmall.com//m/search_items.htm?page_size=20&page_no=' .  $params['page']. '&q=' . urlencode($params['search']);
        } else {
            // 修正采集URL的分页
            $url = str_replace('&page_no=1', '&page_no=' . $params['page'], $params['prop']['cate_data']['prop']);
        }



        $json = '';
        if (empty($json)) {
            halt($url);
        } else {
            $this->getProt($params['id'], $json);
            halt(1);
        }























        $result = $this->snoopy('https:' . $url);
        $result = json_decode($result, true);

        $item = array();
        foreach ($result['item'] as $key => $value) {
            $item[] = array(
                'mall_type' => 'tmall',
                'image'     => !empty($value['img']) ? $value['img'] : $value['vimg'],
                'name'      => $value['title'],
                'url'       => urlencode(str_replace('//detail.tmall.com/', '//detail.m.tmall.com/', $value['url'])),
                'price'     => $value['price'],
            );
        }

        return $item;
    }
}
