<?php
/**
 *
 * 模型基类
 *
 * @package
 * @category  library
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Model.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/05/08
 */
// namespace library;

class Model extends cls_mysql
{
    public $prefix = '';

    private $sql = array(
        'field' => '*',
        'table_name' => '',
        'join' =>'',
        'where' => '',
        'group' => '',
        'order' => '',
        'limit' => '',
        );

    private $last_sql;

    private $filter = array(
        'default' => 'strip_tags,escape_xss',
        'content' => 'escape_xss,htmlspecialchars',
        );

    public function __construct()
    {
        include(CONF_PATH . 'database.php');
        $this->prefix = $prefix;
        $this->cls_mysql($db_host, $db_user, $db_pass, $db_name);
    }

    /**
     * 获得表名
     * @access public
     * @param  string $name
     * @return string
     */
    public function getTable($name)
    {
        return $this->prefix . $name;
    }

    /**
     * 字段
     * @access public
     * @param  string $field
     * @return object
     */
    public function field($field)
    {
        $this->sql['field'] = !empty($field) ? ' ' . $field . ' ' : ' * ';
        return $this;
    }

    /**
     * 表名
     * @access public
     * @param  string $field
     * @return object
     */
    public function table($name)
    {
        $this->sql['table_name'] = ' ' . $this->prefix . $name;
        return $this;
    }

    /**
     * join
     * @access public
     * @param  string $field
     * @return object
     */
    public function join($join)
    {
        $this->sql['join'] = ' ' . $join . ' ';
        return $this;
    }

    /**
     * where
     * @access public
     * @param  string $field
     * @return object
     */
    public function where($map)
    {
        $this->sql['where'] = ' WHERE ' . $map;
        return $this;
    }

    /**
     * group
     * @access public
     * @param  string $field
     * @return object
     */
    public function group($group)
    {
        $this->sql['group'] = ' GROUP BY ' . $group;
        return $this;
    }

    /**
     * order
     * @access public
     * @param  string $field
     * @return object
     */
    public function order($order)
    {
        $this->sql['order'] = ' ORDER BY ' . $order;
        return $this;
    }

    /**
     * limit
     * @access public
     * @param  string $field
     * @return object
     */
    public function limit($limit)
    {
        $this->sql['limit'] = ' LIMIT ' . $limit;
        return $this;
    }

    /**
     * 查询多条信息
     * @access public
     * @param
     * @return array
     */
    public function all()
    {
        $sql = 'SELECT ' . $this->sql['field'] . ' FROM ';
        $sql .= $this->sql['table_name'];
        $sql .= $this->sql['join'];
        $sql .= $this->sql['where'];
        $sql .= $this->sql['group'];
        $sql .= $this->sql['order'];
        $sql .= $this->sql['limit'];
        $this->last_sql = $sql;
        $this->reset();
        return $this->getAll($sql);
    }

    /**
     * 查询单条信息
     * @access public
     * @param
     * @return array
     */
    public function find()
    {
        $sql = 'SELECT ' . $this->sql['field'] . ' FROM ';
        $sql .= $this->sql['table_name'];
        $sql .= $this->sql['join'];
        $sql .= $this->sql['where'];
        $sql .= $this->sql['group'];
        $sql .= $this->sql['order'];
        $sql .= ' LIMIT 1';
        $this->last_sql = $sql;
        $this->reset();
        $result = $this->getRow($sql);
        return empty($result) ? false : $result;
    }

    /**
     * 查询单字段信息
     * @access public
     * @param
     * @return string
     */
    public function one()
    {
        $sql = 'SELECT ' . $this->sql['field'] . ' FROM ';
        $sql .= $this->sql['table_name'];
        $sql .= $this->sql['join'];
        $sql .= $this->sql['where'];
        $sql .= $this->sql['group'];
        $sql .= $this->sql['order'];
        $sql .= ' LIMIT 1';
        $this->last_sql = $sql;
        $this->reset();
        $result = $this->getOne($sql);
        return $result === '' ? false : $result;
    }

    /**
     * 统计数据
     * @access public
     * @param
     * @return int
     */
    public function count()
    {
        $field = $this->sql['field'] !== '*' ? $this->sql['field'] : 'count(1) as count';
        $sql = 'SELECT ' . $field . ' FROM';
        $sql .= $this->sql['table_name'];
        $sql .= $this->sql['join'];
        $sql .= $this->sql['where'];
        $sql .= $this->sql['group'];
        $sql .= $this->sql['order'];
        $sql .= ' LIMIT 1';
        $this->last_sql = $sql;
        $this->reset();
        return $this->getOne($sql);
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
            $field_data[] = $value;
        }

        $field = '`' . implode('`,`', $field) . '`';
        $data = '\'' . implode('\',\'', $field_data) . '\'';

        $sql = 'INSERT INTO ' . $this->sql['table_name'];
        $sql .= '(' . $field . ')';
        $sql .= ' VALUES (' . $data . ')';
        $this->last_sql = $sql;
        $this->reset();
        $this->query($sql);

        return $this->insert_id();
    }

    /**
     * 删除数据
     * @access public
     * @param  array $data
     * @return
     */
    public function delete()
    {
        $sql = 'DELETE FROM ' . $this->sql['table_name'];
        $sql .= $this->sql['where'];
        $this->last_sql = $sql;
        if (empty($this->sql['where'])) {
            echo 'error: sql update where error [' . $this->last_sql . ']';
            exit();
        }
        $this->query($sql);
        $this->reset();
        return true;
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
            $updata_data[] = '`' . $key . '`' . '=\'' . $value . '\'';
        }
        $updata_data = implode(',', $updata_data);
        $sql = 'UPDATE ' . $this->sql['table_name'];
        $sql .= ' SET ' . $updata_data;
        $sql .= $this->sql['where'];
        $this->last_sql = $sql;
        if (empty($this->sql['where'])) {
            echo 'error: sql update where error [' . $this->last_sql . ']';
            exit();
        }
        $this->reset();
        $this->query($sql);
        return true;
    }

    /**
     * 获得执行SQL语句
     * @access public
     * @param
     * @return string
     */
    public function getLastSql()
    {
        $this->reset();
        return $this->last_sql;
    }

    /**
     * 获得表主键
     * @access public
     * @param
     * @return string
     */
    public function getPRI()
    {
        $sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS';
        $sql .= ' WHERE table_name=\'' . trim($this->sql['table_name']) . '\' AND COLUMN_KEY=\'PRI\'';
        return $this->getOne($sql);
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
            'table_name' => '',
            'join' =>'',
            'where' => '',
            'group' => '',
            'order' => '',
            'limit' => '',
            );
    }
}
