<?php
/**
 * 缓存类
 *
 * @package   NiPHPCMS
 * @category  code
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Db.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/08/18
 */
class Cache
{
    public $prefix   = '';        // 缓存前缀
    public $expire   = 300;       // 数据缓存有效期 0表示永久缓存
    public $subdir   = false;     // 缓存子目录(自动根据缓存标识的哈希创建子目录)
    public $check    = true;      // 是否校验缓存
    public $compress = false;     // 是否压缩缓存

    public $type_dir = '';        // 类型目录

    public $open = false;


    public function has($name)
    {
        if (!$this->open) {
            return false;
        }

        $filename = $this->filename($name);
        return is_file($filename);
    }

    /**
     * 读取缓存
     * @access public
     * @param  string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
        $this->delete();

        if (!$this->open) {
            return false;
        }

        $filename = $this->filename($name);
        if (!$this->has($filename)) {
            return false;
        }
        $data = file_get_contents($filename);
        if (false !== $data) {
            $expire = (int) substr($data, 9, 12);
            if ($expire != 0 && time() > filemtime($filename) + $expire) {
                // 缓存过期删除缓存文件
                unlink($filename);
                return false;
            }

            // 开启数据校验
            if ($this->check) {
                $check = substr($data, 21, 32);
                $data = substr($data, 53, -3);
                if ($check != md5($data)) {
                    // 校验错误
                    return false;
                }
            } else {
                $data = substr($data, 21, -3);
            }

            // 启用数据压缩
            if ($this->compress && function_exists('gzcompress')) {
                $data = gzuncompress($data);
            }
            $data = htmlspecialchars_decode($data);
            $data = unserialize($data);
            return $data;
        } else {
            return false;
        }
    }

    /**
     * 写入缓存
     * @access public
     * @param  string $name   缓存变量名
     * @param  mixed  $value  存储数据
     * @return boolen
     */
    public function set($name, $value, $expire = 0)
    {
        if (!$this->open) {
            return false;
        }

        $filename = $this->filename($name);
        $data = serialize($value);
        $data = htmlspecialchars($data);

        // 启用数据压缩
        if ($this->compress && function_exists('gzcompress')) {
            $data = gzcompress($data, 3);
        }

        // 开启数据校验
        if ($this->check) {
            $check = md5($data);
        } else {
            $check = '';
        }

        $expire = $expire ? $expire : $this->expire;
        $data = "<?php\n//>" . sprintf('%012d', $expire) . $check . $data . "\n?>";
        $result = File::create($filename, $data, true);
        if ($result) {
            clearstatcache();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除缓存
     * @access public
     * @param  string $name 缓存变量名
     * @return boolen
     */
    public function delete($name = false)
    {
        if (rand(1, 1000) == 1000) {
            $list = File::get(CACHE_PATH);

            $rand = array_rand($list, 10);
            foreach ($list as $key => $value) {
                if (!in_array($key, $rand)) {
                    unset($list[$key]);
                }
            }

            $days = strtotime('-15 days');
            foreach ($list as $key => $value) {
                if ($value['time'] <= $days) {
                    File::delete(CACHE_PATH . $value['name']);
                }
            }
        }

        if ($name === false) {
            return false;
        }

        $filename = $this->filename($name);
        if (!$this->has($filename)) {
            return false;
        } else {
            return unlink($filename);
        }
    }

    /**
     * 清除缓存
     * @access public
     * @param  string $name 缓存变量名
     * @return boolen
     */
    public function clear()
    {
        if (File::delete(CACHE_PATH)) {
            File::create(CACHE_PATH);
            File::create(CACHE_PATH . 'index.html', '');
            return true;
        } else {
            return false;
        }
    }

    /**
     * 取得变量的存储文件名
     * @access private
     * @param  string $name 缓存变量名
     * @return string
     */
    private function filename($name)
    {
        $name = md5($name);

        File::createDir(CACHE_PATH . $this->type_dir);

        if ($this->subdir) {
            // 使用子目录
            $dir = $this->type_dir . substr($name, 0, 2) . DIRECTORY_SEPARATOR;
            if (!$this->has(CACHE_PATH . $dir . $this->prefix . $name . '.php')) {
                File::create(CACHE_PATH . $dir);
            }

            $filename = $dir . $this->prefix . $name . '.php';
        } else {
            $filename = $this->type_dir . $this->prefix . $name . '.php';
        }

        return CACHE_PATH . $filename;
    }
}