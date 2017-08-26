<?php
/**
 *
 * 翻页操作类
 *
 * @category   Library
 * @package    NiPHPCMS
 * @author     失眠小枕头 [levisun.mail@gmail.com]
 * @copyright  Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version    CVS: $Id: Paging.class.php 2016-01 $
 * @link       http://www.NiPHP.com
 * @since      File available since Release 0.1
 */
class Paging
{
	const VAR_PAGE = 'p';		// 翻页变量名
	private $_totalRows;		// 总行数
	private $_totalPages;		// 总页数
	private $_listRows;			// 列表每页显示行数
	private $_nowPage;			// 当前页数
	private $_rollPage = 2;	// 分页栏每页显示的页数
	private $_url;				// 分页URL地址

	private $_limit = '';		// 数据库查询LIMIT

	/**
	 * 构造方法
	 * @access public
	 * @param  int $totalRows_ 总行数
	 * @param  int $listRows_  每页显示数
	 * @return void
	 */
	public function __construct($totalRows_, $listRows_=10, $page_=1)
	{
		$this->initialize($totalRows_, $listRows_, $page_);
		$this->dispatcher();
		$this->limit();
	}

	public function __get($classVarName_)
	{
		return $this->$classVarName_;
	}

	/**
	 * 初始化变量
	 * @access private
	 * @param  int $totalRows_ 总行数
	 * @param  int $listRows_  每页显示数
	 * @return void
	 */
	private function initialize($totalRows_, $listRows_)
	{
		$this->_totalRows = $totalRows_;
		$this->_listRows = $listRows_;
		$this->_totalPages = ceil($this->_totalRows / $this->_listRows);	// 总页数
		$this->_nowPage = !empty($_GET[self::VAR_PAGE]) ? intval($_GET[self::VAR_PAGE]) : 1;			// 当前页数
		if ($this->_nowPage < 1) {
			$this->_nowPage = 1;
		} elseif (!empty($this->_totalPages) && $this->_nowPage > $this->_totalPages) {
			// $this->_nowPage = $this->_totalPages;
		}
	}

	/**
	 * 分析URL
	 * @access private
	 * @param
	 * @return void
	 */
	private function dispatcher()
	{
		// 分析URL
		$this->_url = $this->domain() . $_SERVER['REQUEST_URI'];
		$par = parse_url($this->_url);
		if (isset($par['query']) && !empty($par['query'])) {
			parse_str($par['query'], $query);
			unset($query[self::VAR_PAGE]);
			$this->_url = $this->domain() . $par['path'] . '?' . http_build_query($query) . '&' . self::VAR_PAGE . '=';
		} else {
			$this->_url = $this->domain() . $par['path'] . '?' . self::VAR_PAGE . '=';
		}
	}

	/**
	 * 获得域名
	 * @access private
	 * @param
	 * @return array
	 */
	private function domain()
	{
		if (isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) {
			$url = 'https://';
		} else {
			$url = 'http://';
		}
		if ('80' != $_SERVER['SERVER_PORT']) {	// 是否是默认端口
			return $url . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
		} else {
			return $url . $_SERVER['SERVER_NAME'];
		}
	}

	/**
	 * LIMIT
	 * @access private
	 * @param
	 * @return string
	 */
	private function limit()
	{
		$this->_limit = ($this->_nowPage - 1) * $this->_listRows . ', ' . $this->_listRows;
	}

	/**
	 * 安类型获取分页信息
	 * @access public
	 * @param
	 * @return html
	 */
	public function getPage($type_='numeric', $ajax_=false)
	{
		if (method_exists($this, $type_)) {
			return $this->$type_($ajax_);
		} else {
			return '未找到' . $type_ . '方法！';
		}
	}

	/**
	 * 获得翻页链接
	 * @access public
	 * @param
	 * @return array
	 */
	public function url()
	{
		if ($this->_nowPage > 1) {
			$row = $this->_nowPage - 1;
			$page['home'] = $this->_url . '1';
			$page['last'] = $this->_url . $row;
		} else {
			$page['home'] = $page['last'] = '';
		}
		if ($this->_totalPages != $this->_nowPage) {
			$row = $this->_nowPage + 1;
			$page['next'] = $this->_url . $row;
			$page['end'] = $this->_url . $this->_totalPages;
		} else {
			$page['next'] = $page['end'] = '';
		}
		return $page;
	}

	/**
	 * 传统翻页
	 * @access private
	 * @param
	 * @return html
	 */
	private function next()
	{
		$page = '';
		if ($this->_nowPage > 1) {
			$row = $this->_nowPage - 1;
			$page .= '<a href="' . $this->_url . '1">' . L('_PAGE.HOME') . '</a>';
			$page .= '<a href="' . $this->_url . $row . '">' . L('_PAGE.LAST') . '</a>';
		} else {
			$page .= '<span>' . L('_PAGE.HOME') . '</span>';
			$page .= '<span>' . L('_PAGE.LAST') . '</span>';
		}
		if ($this->_totalPages != $this->_nowPage) {
			$row = $this->_nowPage + 1;
			$page .= ' <a href="' . $this->_url . $row . '">' . L('_PAGE.NEXT') . '</a>';
			$page .= ' <a href="' . $this->_url . $this->_totalPages . '">' . L('_PAGE.END') . '</a>';
		} else {
			$page .= ' <span>' . L('_PAGE.NEXT') . '</span>';
			$page .= ' <span>' . L('_PAGE.END') . '</span>';
		}
		return $page;
	}

	/**
	 * 数字翻页
	 * @access private
	 * @param
	 * @return html
	 */
	private function numeric($ajax_=false)
	{
		if ($ajax_) {
			// 首页 末页
			$first = $liast = '';
			if ($this->_nowPage > $this->_rollPage + 1) {
				$first = '<a data-url="' . $this->_url . '1">1</a> <span>...</span> ';
			}
			if ($this->_totalPages - $this->_nowPage > $this->_rollPage) {
				$liast = ' <span>...</span> <a data-url="' . $this->_url . $this->_totalPages . '">' . $this->_totalPages . '</a>';
			}

			$linkPage = '';
			for ($i= $this->_nowPage - $this->_rollPage; $i < $this->_nowPage + $this->_rollPage + 1; $i++) {
				if ($i >= 1 && $i <= $this->_totalPages) {
					if ($i == $this->_nowPage) {
						$linkPage .= '<span class="current">' . $i . '</span>';
					} else {
						$linkPage .= '<a data-url="' . $this->_url . $i . '">' . $i . '</a>';
					}
				}
			}
			return $first . $linkPage . $liast;
		}

		// 首页 末页
		$first = $liast = '';
		if ($this->_nowPage > $this->_rollPage + 1) {
			$first = '<a href="' . $this->_url . '1">1</a> <span>...</span> ';
		}
		if ($this->_totalPages - $this->_nowPage > $this->_rollPage) {
			$liast = ' <span>...</span> <a href="' . $this->_url . $this->_totalPages . '">' . $this->_totalPages . '</a>';
		}

		$linkPage = '';
		for ($i= $this->_nowPage - $this->_rollPage; $i < $this->_nowPage + $this->_rollPage + 1; $i++) {
			if ($i >= 1 && $i <= $this->_totalPages) {
				if ($i == $this->_nowPage) {
					$linkPage .= '<span class="current">' . $i . '</span>';
				} else {
					$linkPage .= '<a href="' . $this->_url . $i . '">' . $i . '</a>';
				}
			}
		}
		return $first . $linkPage . $liast;
	}
}