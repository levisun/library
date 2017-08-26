<?php
/**
 * 数据库类
 *
 * @package   NiPHPCMS
 * @category  code
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Db.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/08/18
 */
class Db
{
    private   $db_link;
    protected $db_host;
    protected $db_name;
    protected $db_user;
    protected $db_pass;
    protected $db_prefix;

    public $last_sql;
    public $last_insert_id;

    public function __construct()
    {
        $dsn = 'mysql:host=' . $this->db_host . '; port=3306; dbname=' . $this->db_name;
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_PERSISTENT => false
            );

        try {
            $this->db_link = new PDO($dsn, $this->db_user, $this->db_pass, $options);
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
    }

    /**
     * 执行预处理
     * @access protected
     * @param  string $sql
     * @param  array  $bind
     * @return object
     */
    protected function execute($sql, $bind = array())
    {
        try {
            $this->last_sql = $sql;
            $pdo_statement = $this->db_link->prepare($this->last_sql);
            $pdo_statement->execute($bind);
        } catch (PDOException $e) {
            $error = '[ SQL ] : ' . $this->last_sql . ' [ ' . print_r($bind, true) . ' ]';
            echo $error;
            exit();
        }

        return $pdo_statement;
    }

    /**
     * 新增数据
     * @access public
     * @param
     * @return boolean
     */
    public function add()
    {
        $row = $this->execute($this->last_sql)->rowCount();
        $this->last_insert_id = $this->db_link->lastInsertId();

        return !!$row;
    }

    /**
     * 删除|修改数据
     * @access public
     * @param
     * @return boolean
     */
    public function deleteOrUpdate()
    {
        $row = $this->execute($this->last_sql)->rowCount();

        return !!$row;
    }

    /**
     * 查询数据
     * @access public
     * @param
     * @return array
     */
    public function select()
    {
        $result = $this->execute($this->last_sql)->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * 表数据总数
     * @access public
     * @param
     * @return int
     */
    public function total()
    {
        return $this->execute($this->last_sql)->fetchObject()->count;
    }

    /**
     * 数据下ID
     * @access public
     * @param
     * @return int
     */
    public function nextId()
    {
        $result = $this->execute($this->last_sql)->fetchAll(PDO::FETCH_ASSOC);

        return $result[0]['Auto_increment'];
    }

    /**
     * 数据库表名
     * @access public
     * @param
     * @return array
     */
    public function showTables()
    {
        return $this->execute($this->last_sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 数据库表结构
     * @access public
     * @param
     * @return array
     */
    public function showCreateTable()
    {
        return $this->execute($this->last_sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 数据库表字段属性
     * @access public
     * @param
     * @return array
     */
    public function showColumn()
    {
        return $this->execute($this->last_sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 优化表
     * @access public
     * @param
     * @return string
     */
    public function optimize()
    {
        return $this->execute($this->last_sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 数据库版本
     * @access public
     * @param
     * @return array
     */
    public function version()
    {
        $this->last_sql = 'SELECT version()';

        return $this->execute($this->last_sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 锁表
     * @access public
     * @param  string $lock_type  默认WRITE
     * @return int
     */
    public function lock()
    {
        return $this->execute($this->last_sql);
    }

    /**
     * 解锁表
     * @access public
     * @return int
     */
    public function unlock()
    {
        return $this->execute($this->last_sql)->rowCount();
    }

    /**
     * 增加表字段
     * @access public
     * @return int
     */
    public function addFields()
    {
        return $this->execute($this->last_sql);
    }

    /**
     * 删除表字段
     * @access public
     * @return int
     */
    public function deleteFields()
    {
        return $this->execute($this->last_sql);
    }

    /**
     * 修改表字段
     * @access public
     * @return int
     */
    public function updateFields()
    {
        return $this->execute($this->last_sql);
    }

    public function getLastSql()
    {
        halt($this->last_sql);
    }

    public function __destruct()
    {
        $this->db_link = null;
    }
}
