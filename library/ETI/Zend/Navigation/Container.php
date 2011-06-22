<?php

class ETI_Zend_Navigation_Container extends Zend_Navigation
{

    public function setURI ($label, $uri)
    {
        $this->findOneBy('label', $label)->uri = $uri;
    }

    /**
     * Sets the visible property of a Page to true
     * 
     * @param string $label
     * @param array $properties Any extra properties you might want to set when making the page visible
     */
    public function showPage ($label, $properties = null)
    {
        $page = $this->findOneBy('label', $label);
        $page->setVisible(true);
        $this->setProperties($page, $properties);
    }

    /**
     * Activates a page by giving it another CSS class and a new href attribute
     * 
     * @param string $label
     * @param string $uri The href for the page
     * @param string $cssClass The CSS class for inactive pages
     * @param array $properties Any extra properties you might want to set when making the page active
     */
    public function activatePage ($label, $cssClass, $uri = null, $properties = null)
    {
        $page = $this->findOneBy('label', $label);
        $page->setClass($cssClass);
        if ($uri !== null) {
            $page->uri = $uri;
        }
        $this->setProperties($page, $properties);
    }

    /**
     * Deactivates a page by giving it another CSS class a "do-nothing" href attribute
     * 
     * @param string $label
     * @param string $cssClass The CSS class for inactive pages
     * @param array $properties Any extra properties you might want to set when making the page inactive
     */
    public function deactivatePage ($label, $cssClass, $properties = null)
    {
        $page = $this->findOneBy('label', $label);
        $page->setClass($cssClass);
        $page->uri = 'javascript:;';
        $this->setProperties($page, $properties);
    }

    /**
     * Append a child page to a parent page (e.g. a menu item to a menu).
     * 
     * @param string $parentLabel The label for the parent page
     * @param string $label The label for the page
     * @param string $uri The href attribute for the page
     * @param array $properties Any extra properties you might want to set when adding the page
     */
    public function addChildPage ($parentLabel, $label, $uri, $properties = null)
    {
        if ($properties === null) {
            $properties = array(
                'label' => $label, 
                'type' => 'uri', 
                'uri' => $uri
            );
        }
        else {
            $properties['type'] = 'uri';
            $properties['label'] = $label;
            $properties['uri'] = $uri;
        }
        $parent = $this->findOneBy('label', $parentLabel);
        $child = Zend_Navigation_Page::factory($properties);
        $parent->addPage($child);
    }

    /**
     * Insert a page before or after another one.
     * 
     * @param string $siblingLabel The label of the page after/before which you want to insert
     * @param string $label The label for the page
     * @param string $uri The href attribute for the page
     * @param bool $insertBefore whether to insert the page after or before the specified sibling
     * @param array $properties Any extra properties you might want to set when inserting the page
     */
    public function addSiblingPage ($siblingLabel, $label, $uri, $insertBefore = false, $properties = null)
    {
        if ($properties === null) {
            $properties = array(
                'label' => $label, 
                'type' => 'uri', 
                'uri' => $uri
            );
        }
        else {
            $properties['type'] = 'uri';
            $properties['label'] = $label;
            $properties['uri'] = $uri;
        }
        $item = Zend_Navigation_Page::factory($properties);
        $menu = Bootstrap::instance()->getMenu();
        $sibling = $this->findOneBy('label', $siblingLabel);
        $parent = $sibling->getParent()->toArray();
        $children = $parent['pages'];
        $newChildren = array();
        foreach ($children as $child) {
            if ($child['label'] == $siblingLabel) {
                if ($insertBefore) {
                    $newChildren[] = $item;
                    $newChildren[] = $sibling;
                }
                else {
                    $newChildren[] = $sibling;
                    $newChildren[] = $item;
                }
            }
            else {
                $newChildren[] = $child;
            
            }
        }
        $sibling->getParent()->setPages($newChildren);
    }

    private function setProperties ($page, $properties = null)
    {
        if (is_array($properties)) {
            foreach ($properties as $name => $value) {
                $page->$name = $value;
            }
        }
    }

}