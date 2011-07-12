<?php

class api_models_dao_SourceDatabase extends BaseDAO
{
    private $_id;
    private $_name;
    private $_abbreviatedName;
    private $_groupNameInEnglish;
    private $_authorsAndEditors;
    private $_organisation;
    private $_contactPerson;
    private $_version;
    private $_releaseDate;
    private $_abstract;
    
    private $_coverage;
    private $_completeness;
    private $_confidence;
 
    public function getId ()
    {
        return $this->_id;
    }

    public function setId ($_id)
    {
        $this->_id = $_id;
    }

    public function getName ()
    {
        return $this->_name;
    }

    public function setName ($_name)
    {
        $this->_name = $_name;
    }

    public function getAbbreviatedName ()
    {
        return $this->_abbreviatedName;
    }

    public function setAbbreviatedName ($_abbreviatedName)
    {
        $this->_abbreviatedName = $_abbreviatedName;
    }

    public function getGroupNameInEnglish ()
    {
        return $this->_groupNameInEnglish;
    }

    public function setGroupNameInEnglish ($_groupNameInEnglish)
    {
        $this->_groupNameInEnglish = $_groupNameInEnglish;
    }

    public function getAuthorsAndEditors ()
    {
        return $this->_authorsAndEditors;
    }

    public function setAuthorsAndEditors ($_authorsAndEditors)
    {
        $this->_authorsAndEditors = $_authorsAndEditors;
    }

    public function getOrganisation ()
    {
        return $this->_organisation;
    }

    public function setOrganisation ($_organisation)
    {
        $this->_organisation = $_organisation;
    }

    public function getContactPerson ()
    {
        return $this->_contactPerson;
    }

    public function setContactPerson ($_contactPerson)
    {
        $this->_contactPerson = $_contactPerson;
    }

    public function getVersion ()
    {
        return $this->_version;
    }

    public function setVersion ($_version)
    {
        $this->_version = $_version;
    }

    public function getReleaseDate ()
    {
        return $this->_releaseDate;
    }

    public function setReleaseDate ($_releaseDate)
    {
        $this->_releaseDate = $_releaseDate;
    }

    public function getAbstract ()
    {
        return $this->_abstract;
    }

    public function setAbstract ($_abstract)
    {
        $this->_abstract = $_abstract;
    }
	public function getCoverage ()
    {
        return $this->_coverage;
    }

	public function setCoverage ($_coverage)
    {
        $this->_coverage = $_coverage;
    }

	public function getCompleteness ()
    {
        return $this->_completeness;
    }

	public function setCompleteness ($_completeness)
    {
        $this->_completeness = $_completeness;
    }

	public function getConfidence ()
    {
        return $this->_confidence;
    }

	public function setConfidence ($_confidence)
    {
        $this->_confidence = $_confidence;
    }



}