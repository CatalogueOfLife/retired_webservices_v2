<?php
/**
 * This is a simple extension to the DAO class that takes
 * into account ETI naming conventions when mapping properties
 * to columns. Each uppercase letter in the property name results
 * in an underscore in the column name unless it was preceded
 * by another uppercase letter AND followed by a lowercase letter.
 * The column name itself is all lowercase. E.g.<br/>
 * myUrl -> my_url <br/>
 * myURL -> my_url <br/>
 * myUrlRewriter -> my_url_rewriter <br/>
 * myURLRewriter -> my_url_rewriter <br/><br/>
 * 
 * Also this extension assumes a concrete subclasses of DAO will
 * usually be instantiated with a subclass of Zend_Db_Table_Abstract
 * with the same name, residing in a "tbl" directory next to the
 * directory in which the DAO subclass lives.
 * 
 */


abstract class BaseDAO extends ETI_Zend_Db_DAO
{



    protected function _getTableClass ()
    {
        return str_replace('_dao_', '_tbl_', get_class($this));
    }



    protected function _mapPropertyToColumn ($property)
    {
        $column = '';
        $len = strlen($property);
        for ($i = 0; $i < $len; ++$i) {
            if (ctype_upper($property[$i])) {
                if ($i === $len - 1) {
                    $column .= strtolower($property[$i]);
                }
                else if (ctype_lower($property[$i + 1])) {
                    $column .= '_' . strtolower($property[$i]);
                }
                else {
                    $column .= strtolower($property[$i]);
                }
            }
            else {
                $column .= $property[$i];
            }
        }
        return $column;
    }

}