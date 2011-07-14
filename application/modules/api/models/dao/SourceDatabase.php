<?php

class api_models_dao_SourceDatabase extends BaseDAO
{
    public  $_flushTime;
    public  $_name;
    public  $_abbreviatedName;
    public  $_groupNameInEnglish;
    public  $_authorsAndEditors;
    public  $_organisation;
    public  $_contactPerson;
    public  $_version;
    public  $_releaseDate;
    public  $_abstract;
    public  $_taxonomicCoverage;
    
    public  $_coverage;
    public  $_completeness;
    public  $_confidence;

    public function getFlushTime ()
    {
        return $this->_flushTime;
    }

    public function setFLushTime ($_flushTime)
    {
        $this->_flushTime = $_flushTime;
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

    public function getTaxonomicCoverage ()
    {
        return $this->_taxonomicCoverage;
    }

    public function setTaxonomicCoverage ($_taxonomicCoverage)
    {
        $this->_taxonomicCoverage = $_taxonomicCoverage;
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