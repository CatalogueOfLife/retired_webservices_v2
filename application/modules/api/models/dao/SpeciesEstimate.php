<?php

class api_models_dao_SpeciesEstimate extends BaseDAO
{
    private $_flushTime;
	private $_rank;
    private $_name;
    private $_lastUpdate;
    private $_source;
    private $_speciesEstimate;
 
    public function getFlushTime ()
    {
        return $this->_flushTime;
    }

    public function setFLushTime ($_flushTime)
    {
        $this->_flushTime = $_flushTime;
    }
    
    public function getRank ()
    {
        return $this->_rank;
    }

    public function setRank ($_rank)
    {
        $this->_rank = $_rank;
    }

    public function getName ()
    {
        return $this->_name;
    }

    public function setName ($_name)
    {
        $this->_name = $_name;
    }

    public function getLastUpdate ()
    {
        return $this->_lastUpdate;
    }

    public function setLastUpdate ($_lastUpdate)
    {
        $this->_lastUpdate = $_lastUpdate;
    }

    public function getSource ()
    {
        return $this->_source;
    }

    public function setSource ($_source)
    {
        $this->_source = $_source;
    }

    public function getSpeciesEstimate ()
    {
        return $this->_speciesEstimate;
    }

    public function setSpeciesEstimate ($_speciesEstimate)
    {
        $this->_speciesEstimate = $_speciesEstimate;
    }
}