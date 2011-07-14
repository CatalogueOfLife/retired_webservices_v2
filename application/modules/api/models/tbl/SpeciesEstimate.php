<?php

class api_models_tbl_SpeciesEstimate extends BaseschemaTable {
	protected $_name = '__import_species_estimate';
	protected $_primary = array ('rank', 'name', 'last_update', 'source', 'species_estimate' );
}