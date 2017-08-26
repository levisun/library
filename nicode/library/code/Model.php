<?php
/**
 * 模型类
 *
 * @package   NiPHPCMS
 * @category  code
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Model.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/08/18
 */
class Model extends Db
{
    protected $request;
    protected $cache;

    protected $strtr = array(
        'EQ' => '=',
        'NEQ' => '<>',
        'GT' => '>',
        'EGT' => '>=',
        'LT' => '<',
        'ELT' => '<=',
        'LIKE' => 'LIKE',
        'BETWEEN' => 'BETWEEN',
        'NOT' => 'NOT',
        'IN' => 'IN',
        'NULL' => 'IS NULL',
        );

    protected $sql = array(
        'field' => '*',
        'table' => '',
        'table_ext' => array(),
        'join'  => '',
        'where' => '',
        'group' => '',
        'order' => '',
        'limit' => ' LIMIT 1',
        );

    public function __construct()
    {
        $database = Config::get('database');
        $this->db_host   = $database['db_host'];
        $this->db_name   = $database['db_name'];
        $this->db_user   = $database['db_user'];
        $this->db_pass   = $database['db_pass'];
        $this->db_prefix = $database['db_prefix'];

        parent::__construct();

        $this->request = new Request;
        $this->cache = new Cache;

        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }

    /**
     * 插入新数据
     * @access public
     * @param  array $data
     * @return
     */
    public function save($data)
    {
        foreach ($data as $key => $value) {
            $field[] = $key;

            $value = $value ? trim($value) : $value;
            $value = escape_xss($value);

            if (!get_magic_quotes_gpc() && !empty($value)) {
                $value = addslashes($value);
            }

            $field_data[] = $value;
        }

        $field = '`' . implode('`,`', $field) . '`';
        $data = '\'' . implode('\',\'', $field_data) . '\'';

        $sql = 'INSERT INTO ' . $this->sql['table'];
        $sql .= '(' . $field . ')';
        $sql .= ' VALUES (' . $data . ')';
        $this->last_sql = $sql;
        $this->reset();
        return parent::add();
    }

    /**
     * 删除数据
     * @access public
     * @param  array $data
     * @return
     */
    public function delete()
    {
        $sql = 'DELETE FROM ' . $this->sql['table'];
        $sql .= $this->sql['where'];
        $this->last_sql = $sql;
        if (empty($this->sql['where'])) {
            echo 'error: sql update where error [' . $this->last_sql . ']';
            exit();
        }
        $this->reset();
        return parent::deleteOrUpdate();
    }

    /**
     * 修改数据
     * @access public
     * @param  array $data
     * @return
     */
    public function update($data)
    {
        foreach ($data as $key => $value) {

            $value = trim($value);
            $value = escape_xss($value);

            if (!get_magic_quotes_gpc() && !empty($value)) {
                $value = addslashes($value);
            }

            if ($key == 'exp') {
                $updata_data[] = $value;
            } else {
                $updata_data[] = '`' . $key . '`' . '=\'' . $value . '\'';
            }
        }
        $updata_data = implode(',', $updata_data);

        $sql = 'UPDATE ' . $this->sql['table'];
        $sql .= ' SET ' . $updata_data;
        $sql .= $this->sql['where'];
        $this->last_sql = $sql;
        $this->reset();
        return parent::deleteOrUpdate();
    }

    /**
     * 查询多条信息
     * @access public
     * @param
     * @return array
     */
    public function all()
    {
        $database = Config::get('database');
        $this->cache->open = $database['db_data_cache'];
        $this->cache->type_dir = '';

        $cache_key = NI_MODULE . NI_CONTROLLER . NI_ACTION . __METHOD__;
        $cache_key .= $this->request->url();

        if ($result = $this->cache->get($cache_key)) {
            return $result;
        }

        $sql = 'SELECT ' . $this->sql['field'] . ' FROM ';
        $sql .= $this->sql['table'];
        $sql .= $this->sql['join'];
        $sql .= $this->sql['where'];
        $sql .= $this->sql['group'];
        $sql .= $this->sql['order'];
        $sql .= $this->sql['limit'];
        $this->last_sql = $sql;

        $result = parent::select();
        $this->cache->set($cache_key, $result, $database['db_expire']);
        $this->reset();
        return $result;
    }

    /**
     * 查询单条信息
     * @access public
     * @param
     * @return array
     */
    public function find()
    {
        $result = $this->limit(1)->all();
        return empty($result) ? false : $result[0];
    }

    /**
     * 查询单字段信息
     * @access public
     * @param
     * @return string
     */
    public function one()
    {
        $result = $this->limit(1)->all();
        return empty($result) ? false : array_shift($result[0]);
        return empty($result) ? false : $result[0];
    }

    /**
     * 查询信息是否存在
     * @access public
     * @param
     * @return string
     */
    public function has()
    {
        $result = $this->limit(1)->all();
        $result = empty($result) ? false : $result[0];

        return empty($result) ? false : true;
    }

    /**
     * 表数据总数
     * @access public
     * @param
     * @return int
     */
    public function total()
    {
        $database = Config::get('database');
        $this->cache->open = $database['db_data_cache'];
        $this->cache->type_dir = '';

        $cache_key = NI_MODULE . NI_CONTROLLER . NI_ACTION . __METHOD__;
        $cache_key .= $this->request->url();

        if ($result = $this->cache->get($cache_key)) {
            return $result;
        }

        $sql = 'SELECT count(1) AS count FROM ';
        $sql .= $this->sql['table'];
        $sql .= $this->sql['join'];
        $sql .= $this->sql['where'];
        $sql .= $this->sql['group'];
        $sql .= $this->sql['order'];
        $sql .= $this->sql['limit'];
        $this->last_sql = $sql;

        $result = parent::total();

        $this->cache->set($cache_key, $result, $database['db_expire']);
        $this->reset();

        return $result;
    }

    /**
     * 数据下ID
     * @access public
     * @param
     * @return int
     */
    public function nextId()
    {
        $sql = 'SHOW TABLE STATUS LIKE \'' . trim($this->sql['table']) . '\'';
        $this->last_sql = $sql;
        $this->reset();
        return parent::nextId();
    }

    public function join($table, $on, $field = '', $type = 'INNER JOIN')
    {
        $table = $this->db_prefix . $table;

        $result = explode(' as ', strtolower($table));
        if (count($result) == 2) {
            $table = '`' . $result[0] . '` as `' . $result[1] . '`';
            $table_ext = array(
                'name' => $result[0],
                'ext' => $result[1],
            );
        } else {
            $table = '`' . $result[0] . '`';
            $table_ext = array(
                'name' => $result[0],
                'ext' => $result[0],
            );
        }

        if ($field != '') {
            $result = explode(',', $field);
            foreach ($result as $key => $value) {
                $result[$key] = '`' . $table_ext['ext'] .  '`.`' . $value . '`';
            }

            $field = implode(',', $result);
        }

        $this->sql['join'] .= ' ' . $type . ' ' . $table . ' ON(' . $on . ')';
        $this->sql['field'] .= ',' . $field;

        return $this;
    }

    public function where($map)
    {
        $this->sql['where'] = ' WHERE ';
        if (!is_array($map)) {
            $this->sql['where'] .= $map;
        } else {
            $new_map = array();
            foreach ($map as $key => $value) {
                if (!is_array($value)) {
                    $value = '`' . $key . '`=' . $value;
                    $new_map[] = $value;
                } else {
                    if (in_array($key, array('EXP', 'exp'))) {
                        foreach ($value[1] as $k => $val) {
                            if (!is_array($val)) {
                                $val = '`' . $k . '`=' . $val;
                            } else {
                                if (in_array($val[0], array('BETWEEN', 'IN'))) {
                                    $val[1] = '(' . $val[1] . ')';
                                }
                                $val = '`' . $k . '` ' . implode(' ', $val);
                                $val = strtr($val, $this->strtr);
                            }
                            $new_map[$value[0]][] = $val;
                        }
                    } else {
                        if (in_array($value[0], array('BETWEEN', 'IN'))) {
                            $value[1] = '(' . $value[1] . ')';
                        }
                        $value = '`' . $key . '` ' . implode(' ', $value);
                        $value = strtr($value, $this->strtr);
                        $new_map[] = $value;
                    }
                }
            }

            $where = '';
            foreach ($new_map as $key => $value) {
                if (is_string($key)) {
                    if (count($value) == 1) {
                        $where .= ' ' . $key . ' ' . $value[0];
                    } else {
                        $where .= ' AND (' . implode(' ' . $key . ' ', $value) . ')';
                    }
                } else {
                    $where .= ' AND ' . $value;
                }
            }

            $this->sql['where'] .= trim($where, ' AND ');
        }

        return $this;
    }

    /**
     * limit
     * @access public
     * @param  string $limit
     * @return object
     */
    public function limit($limit)
    {
        $this->sql['limit'] = ' LIMIT ' . $limit;
        return $this;
    }

    /**
     * order
     * @access public
     * @param  string $order
     * @return object
     */
    public function order($order)
    {
        $this->sql['order'] = ' ORDER BY ' . $order;
        return $this;
    }

    /**
     * group
     * @access public
     * @param  string $group
     * @return object
     */
    public function group($group)
    {
        $this->sql['group'] = ' GROUP BY ' . $group;
        return $this;
    }

    /**
     * field
     * @access public
     * @param  string $field
     * @return object
     */
    public function field($field)
    {
        if ($field == '*' && $field === true) {
            if (count($this->sql['table_ext']) == 2) {
                $field = $this->getTableField('`' . $this->sql['table_ext']['name'] . '`', '`' . $this->sql['table_ext']['ext'] . '`');
            } else {
                $field = $this->getTableField('`' . $this->sql['table_ext']['name'] . '`');
            }
        }

        // 组合字段
        $this->sql['field'] = $field;

        return $this;
    }

    /**
     * table
     * @access public
     * @param  string $name
     * @return object
     */
    public function table($name)
    {
        $name = $this->db_prefix . $name;

        $result = explode(' as ', strtolower($name));
        if (count($result) == 2) {
            $this->sql['table'] = '`' . $result[0] . '` as `' . $result[1] . '`';
            $this->sql['table_ext'] = array(
                'name' => $result[0],
                'ext' => $result[1],
            );
        } else {
            $this->sql['table'] = '`' . $result[0] . '`';
            $this->sql['table_ext'] = array(
                'name' => $result[0]
            );
        }

        return $this;
    }

    /**
     * 获得表字段
     * @access private
     * @param  string  $table 表名
     * @param  string  $ext   表另名
     * @return string
     */
    private function getTableField($table, $ext = '')
    {
        $database = Config::get('database');
        $this->cache->open = $database['db_field_cache'];
        $this->cache->type_dir = 'table_field' . DIRECTORY_SEPARATOR;

        $this->last_sql = 'SHOW COLUMNS FROM ' . $table;

        if ($field = $this->cache->get($this->last_sql)) {
            return $field;
        }

        $result = parent::showColumn();

        $field = array();
        foreach ($result as $key => $value) {
            $field[] = $value['Field'];
        }
        $ext = $ext ? $ext . '.' : '';
        $field = $ext . '`' . implode('`,' . $ext . '`' , $field) . '`';

        $this->cache->set($this->last_sql, $field, $database['db_expire']);
        $this->cache->type_dir = '';

        return $field;
    }

    private function cache()
    {
        # code...
    }

    /**
     * 复位
     * @access private
     * @param
     * @return void
     */
    private function reset()
    {
        $this->sql = array(
            'field' => '*',
            'table' => '',
            'join'  =>'',
            'where' => '',
            'group' => '',
            'order' => '',
            'limit' => '1',
            );
    }
}
