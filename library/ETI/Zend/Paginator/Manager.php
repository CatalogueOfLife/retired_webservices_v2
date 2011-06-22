<?php
/**
 * This class manages all pagination for you, provided that you do not have
 * very peculiar search or sort requirements.
 * 
 * ASSUMPTIONS:
 * ------------
 * o You use ETI_Zend_Db_DAO as the data provider (via its loadXXX methods). 
 * o The request contains a "sortColumn" parameter that indicates what column
 * to sort on.
 * o The request contains a "sortDirection" parameter that indicates whether
 * to sort ascending or descending.
 * o The request contains a "searchTerm" parameter that you are searching for.
 * o The request contains a "page" parameter that indicates the current page number.
 * o If the request contains a parameter called "itemCount", it will be used
 * as the total number of items to paginate through. This allows you to cache
 * that number, rather than have it recalculated for every request.
 * 
 * The PaginationManager does the following for you:
 * o It creates a Zend_Paginator and sets it on the view object (as 'paginator').
 * o It places the page number in the request back on the view object (as 'page').
 * o It places the sort column in the request back on the view object (as 'sortColumn').
 * o It places the sort direction in the request back on the view object (as 'sortDirection').
 * o It places the search term in the request back on the view object (as 'searchTerm').
 * o It sets the number of pages on the view object (as 'pageCount').
 * o It sets total number of rows on the view object (as 'itemCount').
 * 
 * LIMITATIONS:
 * ------------
 * o You can only have one search term (but you can search through multiple columns).
 * o It only works in conjunction with the ETI_Zend_Db_DAO persitency layer.
 * 
 * @author ayco
 *
 */
class ETI_Zend_Paginator_Manager
{
    
    const DEFAULT_PAGE_SIZE = 20;
    
    /**
     * @var Zend_Controller_Action
     */
    private $_controller;
    /**
     * @var ETI_Zend_Paginator_DAOAdapter
     */
    private $_adapter;
    
    private $_searchColumns;
    private $_defaultSortColumn;
    private $_whereTemplate;
    private $_orderByTemplate;
    private $_pageSize = self::DEFAULT_PAGE_SIZE;
    private $_itemCount = -1;
    private $_searchWordStartOnly = false;

    public function __construct (Zend_Controller_Action $controller, ETI_Zend_Db_DAO $dao)
    {
        $this->_controller = $controller;
        $this->_adapter = new ETI_Zend_Paginator_DAOAdapter($dao);
    }

    public function setup ()
    {
        $page = $this->_param('page', 1, 1);
        $itemCount = $this->_itemCount >= 0 ? $this->_itemCount : $this->_param('itemCount', -1, -1);
        
        $this->_adapter->setWhere($this->_getWhereClause());
        $this->_adapter->setOrderBy($this->_getOrderByClause());
        $this->_adapter->setItemCount($itemCount);
        
        $paginator = new Zend_Paginator($this->_adapter);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($this->_pageSize);
        
        $itemCount = $paginator->getTotalItemCount();
        
        $pageCount = (int) ceil($itemCount / $this->_pageSize);
        
        $this->_controller->view->paginator = $paginator;
        $this->_controller->view->page = $page;
        $this->_controller->view->itemCount = $itemCount;
        $this->_controller->view->pageCount = $pageCount;
        $this->_controller->view->sortColumn = $this->_param('sortColumn', $this->_defaultSortColumn);
        $this->_controller->view->sortDirection = $this->_param('sortDirection', 'ASC', 'ASC');
        $this->_controller->view->searchTerm = $this->_param('searchTerm', null);
    }

    /**
     * Set the total number of rows returned by the query (without LIMIT clause).
     * 
     * If there is an itemCount parameter in the request it will automatically
     * be picked up by the PaginationManager. However, this method allows you to
     * set the total number of rows manually, for example when you have stored
     * that number in the session rather than in a hidden input field. It there
     * is no itemCount parameter in the request and you don't call this method
     * either, the total number of rows is calculated using a SELECT COUNT(*)
     * query.
     * 
     * @param $i
     */
    public function setItemCount ($i)
    {
        $this->_itemCount = $i;
    }

    /**
     * Set the number of rows per page. If you do not call this method, the
     * defaultPageSize setting in application.ini is used.
     * 
     * @param $size
     */
    public function setPageSize ($size)
    {
        $this->_pageSize = $size;
    }

    /**
     * Whether or not the search term must match the beginning of the column value.
     * 
     * If true, an index placed on that column is more likely to be used by the DBMS.
     * 
     * @param $bool
     */
    public function setSearchWordStartOnly ($bool = false)
    {
        $this->_searchWordStartOnly = $bool;
    }

    /**
     * Set the database columns that should be queried using the
     * search term entered by the user. You may supply multiple search
     * columns, either by passing an array, or by supplying extra
     * arguments.
     * 
     * @param $column The column(s) to be searched
     */
    public function setSearchColumn ($column)
    {
        $this->_searchColumns = is_array($column) ? $column : func_get_args();
    }

    /**
     * Set the column that we should sort on if there is no sortColumn
     * parameter in the request.
     * 
     * @param string $column
     */
    public function setDefaultSortColumn ($column)
    {
        $this->_defaultSortColumn = $column;
    }

    /**
     * Set an sprintf template to embed the WHERE clause in.
     * 
     * The default WHERE clause is generated from the search term
     * and the columns to search in. If you need extra conditions
     * in your WHERE clause, you can do so like this:
     * 
     * "last_name = 'Smith' AND %s"
     * 
     * In this example only records where last_name equals "Smith"
     * are returned, whatever the search term entered by the user.
     * 
     * 
     * @param string $template
     */
    public function setWhereTemplate ($template)
    {
        $this->_whereTemplate = $template;
    }

    /**
     * Set an sprintf template to embed the ORDER BY clause in.
     * 
     * The default ORDER BY clause is generated from the sortColumn and
     * sortDirection request parameters.
     * 
     * Example: "first_name, %s, last_name DESC".
     * 
     * In this example the sort column coming in from the request is actually
     * just the second column to sort on.
     * 
     * @param string $template
     */
    public function setOrderByTemplate ($template)
    {
        $this->_orderByTemplate = $template;
    }

    /**
     * If you want to search through a result set that is generated
     * through the main dao's "loadChildren" method, rather than
     * through the "loadMultiple1" method, call this method.
     * 
     * @param $dao
     */
    public function setChildDao (ETI_Zend_Db_DAO $dao)
    {
        $this->_adapter->setChildDAO($dao);
    }

    /**
     * If you want to search through a result set that is generated
     * through the main dao's "loadManyToMany" method, rather than
     * through the "loadMultiple1" method, call this method. You must
     * then first call the setChildDAO method to set the DAO that
     * represents the intersection table.
     * 
     * @param $dao
     */
    public function setManyToManyDAO (ETI_Zend_Db_DAO $dao)
    {
        $this->_adapter->setManyToManyDAO($dao);
    }

    private function _getWhereClause ()
    {
        $searchTerm = $this->_param('searchTerm', null);
        if ($searchTerm === null || $this->_searchColumns === null) {
            if ($this->_whereTemplate !== null) {
                // fill in an always-true condition (like '1' or
                // '1=1') in the designated slot in the template
 
                return sprintf($this->_whereTemplate, '1');
            }
            return null;
        }
        $where = array();
        $where[] = '(';
        foreach ($this->_searchColumns as $i => $sc) {
            if ($i > 0) {
                $where[] = ' OR ';
            }
            $where[] = '(' . $sc . ' LIKE \'';
            if (!$this->_searchWordStartOnly) {
                $where[] = '%';
            }
            $where[] = $searchTerm . '%\')';
        }
        $where[] = ')';
        if ($this->_whereTemplate !== null) {
            return sprintf($this->_whereTemplate, implode('', $where));
        }
        return implode('', $where);
    }

    private function _getOrderByClause ()
    {
        $sortColumn = $this->_param('sortColumn', $this->_defaultSortColumn, $this->_defaultSortColumn);
        if ($sortColumn === null) {
            if ($this->_orderByTemplate !== null) {
                return $this->_orderByTemplate;
            }
        }
        else {
            $sortDirection = $this->_param('sortDirection', 'ASC', 'ASC');
            $orderBy = $sortColumn . ' ' . $sortDirection;
            if ($this->_orderByTemplate !== null) {
                $orderBy = sprintf($this->_orderByTemplate, $orderBy);
            }
            return $orderBy;
        }
    }

    private function _param ($key, $whenAbsent = false, $whenNull = null)
    {
        $source = $this->_controller->getRequest()->getParams();
        if (array_key_exists($key, $source)) {
            $value = $source[$key];
            if ((is_string($value) && strlen($value) === 0) || ($value === null)) {
                return $whenNull;
            }
            return $value;
        }
        return $whenAbsent;
    }

}