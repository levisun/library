<?php
/**
 * 分页
 *
 * @package
 * @category  library\extend\util
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Page.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/05/08
 */
// namespace extend\util;

class Page
{
    // 最后一页
    protected $last_page;
    // 数据总数
    protected $total;
    // 当前页
    protected $current_page;
    // 是否有下一页
    protected $has_more;
    //
    protected $list_rows;

    protected $page_var  = 'p';
    protected $url = '';
    public    $simple = false;
    public    $limit;

    public function paginate($total, $list_rows=10)
    {
        $this->total        = $total;
        $this->list_rows    = $list_rows;
        $this->last_page    = (int) ceil($total / $list_rows);
        $this->current_page = Base::input('get.' . $this->page_var, 1);
        $this->has_more     = $this->current_page < $this->last_page;

        $this->limit = ($this->current_page - 1) * $this->list_rows;
        $this->limit .= ', ' . $this->list_rows;
    }

    /**
     * 渲染分页html
     * @return mixed
     */
    public function render()
    {
        if ($this->simple) {
            return sprintf(
                '<ul class="pager">%s %s</ul>',
                $this->getPreviousButton(),
                $this->getNextButton()
            );
        } else {
            return sprintf(
                '<ul class="pagination">%s %s %s</ul>',
                $this->getPreviousButton(),
                $this->getLinks(),
                $this->getNextButton()
            );
        }
    }

    /**
     * 上一页按钮
     * @param string $text
     * @return string
     */
    protected function getPreviousButton($text = "&laquo;")
    {

        if ($this->current_page <= 1) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url($this->current_page - 1);

        return $this->getPageLinkWrapper($url, $text);
    }

    /**
     * 下一页按钮
     * @param string $text
     * @return string
     */
    protected function getNextButton($text = '&raquo;')
    {
        if (!$this->has_more) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url($this->current_page + 1);

        return $this->getPageLinkWrapper($url, $text);
    }

    /**
     * 页码按钮
     * @return string
     */
    protected function getLinks()
    {
        $block = array(
            'first'  => null,
            'slider' => null,
            'last'   => null
        );

        $side   = 3;
        $window = $side * 2;

        if ($this->last_page < $window + 6) {
            $block['first'] = $this->getUrlRange(1, $this->last_page);
        } elseif ($this->currentPage <= $window) {
            $block['first'] = $this->getUrlRange(1, $window + 2);
            $block['last']  = $this->getUrlRange($this->last_page - 1, $this->last_page);
        } elseif ($this->currentPage > ($this->last_page - $window)) {
            $block['first'] = $this->getUrlRange(1, 2);
            $block['last']  = $this->getUrlRange($this->last_page - ($window + 2), $this->last_page);
        } else {
            $block['first']  = $this->getUrlRange(1, 2);
            $block['slider'] = $this->getUrlRange($this->currentPage - $side, $this->currentPage + $side);
            $block['last']   = $this->getUrlRange($this->last_page - 1, $this->last_page);
        }

        $html = '';

        if (is_array($block['first'])) {
            $html .= $this->getUrlLinks($block['first']);
        }

        if (is_array($block['slider'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['slider']);
        }

        if (is_array($block['last'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['last']);
        }

        return $html;
    }

    /**
     * 生成一个可点击的按钮
     *
     * @param  string $url
     * @param  int    $page
     * @return string
     */
    protected function getAvailablePageWrapper($url, $page)
    {
        return '<li><a href="' . htmlentities($url) . '">' . $page . '</a></li>';
    }

    /**
     * 生成一个禁用的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<li class="disabled"><span>' . $text . '</span></li>';
    }

    /**
     * 生成一个激活的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<li class="active"><span>' . $text . '</span></li>';
    }

    /**
     * 生成省略号按钮
     *
     * @return string
     */
    protected function getDots()
    {
        return $this->getDisabledTextWrapper('...');
    }

    /**
     * 批量生成页码按钮.
     *
     * @param  array $urls
     * @return string
     */
    protected function getUrlLinks(array $urls)
    {
        $html = '';

        foreach ($urls as $page => $url) {
            $html .= $this->getPageLinkWrapper($url, $page);
        }

        return $html;
    }

    /**
     * 生成普通页码按钮
     *
     * @param  string $url
     * @param  int    $page
     * @return string
     */
    protected function getPageLinkWrapper($url, $page)
    {
        if ($page == $this->current_page) {
            return $this->getActivePageWrapper($page);
        }

        return $this->getAvailablePageWrapper($url, $page);
    }

    /**
     * 创建一组分页链接
     *
     * @param  int $start
     * @param  int $end
     * @return array
     */
    public function getUrlRange($start, $end)
    {
        $urls = array();

        for ($page = $start; $page <= $end; $page++) {
            $urls[$page] = $this->url($page);
        }

        return $urls;
    }

    /**
     *
     * @access private
     * @param
     * @return string
     */
    private function url($page=0)
    {
        $url = !empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http://';
        $url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $url = parse_url($url);

        $query = explode('&', $_SERVER['QUERY_STRING']);
        $params = array();
        foreach ($query as $key => $value) {
            $item = explode('=', $value);
            $params[$item[0]] = $item[1];
        }
        unset($params[$this->page_var]);

        $url['query'] = http_build_query($params);

        $this->url = $url['scheme'] . '://' . $url['host'] . $url['path'] . '?' . $url['query'];

        $this->url .= !empty($page) ? '&' . $this->page_var . '=' . $page : '';

        return $this->url;
    }
}
