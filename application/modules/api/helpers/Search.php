<?php

class api_helpers_Search
{
    protected $_version = '1.0';
    protected $_encoding = 'UTF-8';
    protected $_preserveWhiteSpace = false;
    protected $_formatOutput = true;
    protected $_db;

    public function selectSynonyms ($genus, $species, $infraspecies, $version, $format, $key)
    {
        $found = false;
        $continue = true;
        
        $dom = new DOMDocument($this->_version, $this->_encoding);
        $dom->preserveWhiteSpace = $this->_preserveWhiteSpace;
        $dom->formatOutput = $this->_formatOutput;
        
        $node_ws_status = $dom->createElement("status");
        
        $node_sp2000 = $dom->createElement("sp2000");
        $node_request = $dom->createElement("request");
        $node_service = $dom->createElement("service");
        $node_description = $dom->createElement("description");
        $node_parameters = $dom->createElement("parameters");
        $node_genus = $dom->createElement("genus");
        $node_species = $dom->createElement("species");
        $node_infraspecies = $dom->createElement("infraspecies");
        $node_version = $dom->createElement("version");
        $node_date = $dom->createElement("date");
        
        $node_service->setAttribute("id", "synonyms");
        
        $node_genus->appendChild($this->_createFormatedText($dom, $genus));
        $node_species->appendChild($this->_createFormatedText($dom, $species));
        $node_infraspecies->appendChild($this->_createFormatedText($dom, $infraspecies));
        $node_version->appendChild($this->_createFormatedText($dom, $version));
        
        $node_description->appendChild($this->_createFormatedText($dom, "Taxon synonyms in CoL"));
        
        $current_date_time = gmdate("d M Y H:i:s T");
        $node_date->appendChild($this->_createFormatedText($dom, $current_date_time));
        
        $node_parameters->appendChild($node_genus);
        $node_parameters->appendChild($node_species);
        $node_parameters->appendChild($node_infraspecies);
        $node_parameters->appendChild($node_version);
        
        $node_service->appendChild($node_description);
        $node_service->appendChild($node_parameters);
        $node_service->appendChild($node_date);
        
        $node_request->appendChild($node_service);
        
        $node_sp2000->appendChild($node_request);
        
        try
        {
            if ((!empty($genus)) && (!empty($key)) && !(empty($species) && !empty($infraspecies)))
            {                 
                try
                {
                    $resource = Bootstrap::instance()->getPluginResource('multidb');
                    $this->_db = $resource->getDb('application');
                    
                    $sql = " SELECT COUNT(*) FROM key_store WHERE service_key = '" . $key . "' ";
                    $count = (int)$this->_db->fetchOne($sql);
                    
                    if ($count == 0)
                    {
                        $node_ws_status->setAttribute("id", "500");
                        $node_ws_status->appendChild($this->_createFormatedText($dom, "Invalid key."));
                        $continue = false;
                    }
                }
                catch (Exception $e)
                {
                    $node_ws_status->setAttribute("id", "400");
                    $node_ws_status->appendChild($this->_createFormatedText($dom, "Unable to verify the validity of the key."));
                    $continue = false;
                }
                
                if ($continue)
                {            
                    try
                    {
                        $resource = Bootstrap::instance()->getPluginResource('multidb');
                        $this->_db = $resource->getDb('baseschema');
                    }
                    catch (Exception $e)
                    {
                        $node_ws_status->setAttribute("id", "200");
                        $node_ws_status->appendChild($this->_createFormatedText($dom, "Unable to connect to the specified database."));
                        $continue = false;
                    }
                }
                
                if ($continue) {
                
                    $node_response = $dom->createElement("response");
                    
                    $sql = " SELECT ssac.id, ssac.kingdom, ssac.phylum, ssac.class, ssac.`order`, ssac.superfamily, ssac.family, ssac.genus, 
                                    ssac.subgenus, ssac.species, ssac.infraspecific_marker, ssac.infraspecies, ssac.author
                             FROM _search_scientific ss
                                LEFT JOIN _search_scientific ssac ON (ss.accepted_species_id = ssac.id OR (ss.status IN (0,1,4) AND ss.id = ssac.id))
                             WHERE ss.genus = '" . $genus . "' ";
                    
                    if (!empty($species))
                    {
                        $sql .= " AND ss.species = '" . $species . "' ";
                    }
                    else
                    {
                        $sql .= " AND (ss.species IS NULL OR ss.species = '') ";
                    }
                    
                    if (!empty($infraspecies))
                    {
                        $sql .= " AND ss.infraspecies = '" . $infraspecies . "' ";
                    }
                    else
                    {
                        $sql .= " AND (ss.infraspecies IS NULL OR ss.infraspecies = '') ";
                    }
                    
                    $accepted = $this->_db->query($sql);
                    $rows_accepted = $accepted->fetchAll();
                    
                    foreach ($rows_accepted as $row_ac)
                    {
                        $found = true;
                        
                        $node_accepted = $dom->createElement("accepted_name");
                        $node_accepted->setAttribute("id", $row_ac["id"]);
                        
                        $node_kingdom = $dom->createElement("kingdom");
                        $node_kingdom->appendChild($this->_createFormatedText($dom, $row_ac["kingdom"]));
                        $node_phylum = $dom->createElement("phylum");
                        $node_phylum->appendChild($this->_createFormatedText($dom, $row_ac["phylum"]));
                        $node_class = $dom->createElement("class");
                        $node_class->appendChild($this->_createFormatedText($dom, $row_ac["class"]));
                        $node_order = $dom->createElement("order");
                        $node_order->appendChild($this->_createFormatedText($dom, $row_ac["order"]));
                        $node_superfamily = $dom->createElement("superfamily");
                        $node_superfamily->appendChild($this->_createFormatedText($dom, $row_ac["superfamily"]));
                        $node_family = $dom->createElement("family");
                        $node_family->appendChild($this->_createFormatedText($dom, $row_ac["family"]));
                        $node_genus = $dom->createElement("genus");
                        $node_genus->appendChild($this->_createFormatedText($dom, $row_ac["genus"]));
                        $node_subgenus = $dom->createElement("subgenus");
                        $node_subgenus->appendChild($this->_createFormatedText($dom, $row_ac["subgenus"]));
                        $node_species = $dom->createElement("species");
                        $node_species->appendChild($this->_createFormatedText($dom, $row_ac["species"]));
                        $node_infraspecific_marker = $dom->createElement("infraspecific_marker");
                        $node_infraspecific_marker->appendChild($this->_createFormatedText($dom, $row_ac["infraspecific_marker"]));
                        $node_infraspecies = $dom->createElement("infraspecies");
                        $node_infraspecies->appendChild($this->_createFormatedText($dom, $row_ac["infraspecies"]));
                        $node_author = $dom->createElement("author");
                        $node_author->appendChild($this->_createFormatedText($dom, $row_ac["author"]));
                        
                        $node_accepted->appendChild($node_kingdom);
                        $node_accepted->appendChild($node_phylum);
                        $node_accepted->appendChild($node_class);
                        $node_accepted->appendChild($node_order);
                        $node_accepted->appendChild($node_superfamily);
                        $node_accepted->appendChild($node_family);
                        $node_accepted->appendChild($node_genus);
                        $node_accepted->appendChild($node_subgenus);
                        $node_accepted->appendChild($node_species);
                        $node_accepted->appendChild($node_infraspecific_marker);
                        $node_accepted->appendChild($node_infraspecies);
                        $node_accepted->appendChild($node_author);
                        
                        $sql = " SELECT ss.id, kingdom, phylum, class, `order`, superfamily, family, genus, 
                                    subgenus, species, infraspecific_marker, infraspecies, author, sns.name_status
                                FROM _search_scientific ss
                                    INNER JOIN scientific_name_status sns ON (ss.status = sns.id)
                                WHERE accepted_species_id = " . $row_ac["id"] . "
                                 ORDER BY kingdom, phylum, class, `order`, superfamily, family, genus, 
                                    subgenus, species, infraspecific_marker, infraspecies, author ";
                        
                        $node_synonyms = $dom->createElement("synonyms");
                        
                        $rows_synonym = $this->_db->query($sql);
                        while ($row_sy = $rows_synonym->fetch())
                        {
                            $node_synonym = $dom->createElement("synonym");
                            $node_synonym->setAttribute("id", $row_sy["id"]);
                            
                            $node_status = $dom->createElement("status");
                            $node_status->appendChild($this->_createFormatedText($dom, $row_sy["name_status"]));
                            $node_kingdom = $dom->createElement("kingdom");
                            $node_kingdom->appendChild($this->_createFormatedText($dom, $row_sy["kingdom"]));
                            $node_phylum = $dom->createElement("phylum");
                            $node_phylum->appendChild($this->_createFormatedText($dom, $row_sy["phylum"]));
                            $node_class = $dom->createElement("class");
                            $node_class->appendChild($this->_createFormatedText($dom, $row_sy["class"]));
                            $node_order = $dom->createElement("order");
                            $node_order->appendChild($this->_createFormatedText($dom, $row_sy["order"]));
                            $node_superfamily = $dom->createElement("superfamily");
                            $node_superfamily->appendChild($this->_createFormatedText($dom, $row_sy["superfamily"]));
                            $node_family = $dom->createElement("family");
                            $node_family->appendChild($this->_createFormatedText($dom, $row_sy["family"]));
                            $node_genus = $dom->createElement("genus");
                            $node_genus->appendChild($this->_createFormatedText($dom, $row_sy["genus"]));
                            $node_subgenus = $dom->createElement("subgenus");
                            $node_subgenus->appendChild($this->_createFormatedText($dom, $row_sy["subgenus"]));
                            $node_species = $dom->createElement("species");
                            $node_species->appendChild($this->_createFormatedText($dom, $row_sy["species"]));
                            $node_infraspecific_marker = $dom->createElement("infraspecific_marker");
                            $node_infraspecific_marker->appendChild(
                            $this->_createFormatedText($dom, $row_sy["infraspecific_marker"]));
                            $node_infraspecies = $dom->createElement("infraspecies");
                            $node_infraspecies->appendChild($this->_createFormatedText($dom, $row_sy["infraspecies"]));
                            $node_author = $dom->createElement("author");
                            $node_author->appendChild($this->_createFormatedText($dom, $row_sy["author"]));
                            
                            $node_synonym->appendChild($node_status);
                            $node_synonym->appendChild($node_kingdom);
                            $node_synonym->appendChild($node_phylum);
                            $node_synonym->appendChild($node_class);
                            $node_synonym->appendChild($node_order);
                            $node_synonym->appendChild($node_superfamily);
                            $node_synonym->appendChild($node_family);
                            $node_synonym->appendChild($node_genus);
                            $node_synonym->appendChild($node_subgenus);
                            $node_synonym->appendChild($node_species);
                            $node_synonym->appendChild($node_infraspecific_marker);
                            $node_synonym->appendChild($node_infraspecies);
                            $node_synonym->appendChild($node_author);
                            
                            $node_synonyms->appendChild($node_synonym);
                        }
                        
                        $node_accepted->appendChild($node_synonyms);
                        $node_response->appendChild($node_accepted);
                    }
                    
                    if ($found)
                    {
                        $node_ws_status->setAttribute("id", "0");
                        $node_ws_status->appendChild($this->_createFormatedText($dom, "Success"));
                    }
                    else
                    {
                        $node_ws_status->setAttribute("id", "100");
                        $node_ws_status->appendChild($this->_createFormatedText($dom, "Name not found"));
                    }
                    
                    $node_sp2000->appendChild($node_response);
                }
            }
            else
            {
                $node_ws_status->setAttribute("id", "300");
                $node_ws_status->appendChild($this->_createFormatedText($dom, "Required parameters not specified"));
            }
        }
        catch (Exception $e)
        {
            $node_ws_status->setAttribute("id", "600");
            $node_ws_status->appendChild($this->_createFormatedText($dom, $e->getMessage()));
        }
        
        $node_sp2000->appendChild($node_ws_status);
        $dom->appendChild($node_sp2000);
        
        return htmlspecialchars_decode($dom->saveXML(), ENT_NOQUOTES);
    }

    public function selectCommonNames ($genus, $species, $infraspecies, $version, $format, $key)
    {
        $found = false;
        $continue = true;
        
        $dom = new DOMDocument($this->_version, $this->_encoding);
        $dom->preserveWhiteSpace = $this->_preserveWhiteSpace;
        $dom->formatOutput = $this->_formatOutput;
        
        $node_ws_status = $dom->createElement("status");
        
        $node_sp2000 = $dom->createElement("sp2000");
        $node_request = $dom->createElement("request");
        $node_service = $dom->createElement("service");
        $node_description = $dom->createElement("description");
        $node_parameters = $dom->createElement("parameters");
        $node_genus = $dom->createElement("genus");
        $node_species = $dom->createElement("species");
        $node_infraspecies = $dom->createElement("infraspecies");
        $node_version = $dom->createElement("version");
        $node_date = $dom->createElement("date");
        
        $node_service->setAttribute("id", "common");
        
        $node_genus->appendChild($this->_createFormatedText($dom, $genus));
        $node_species->appendChild($this->_createFormatedText($dom, $species));
        $node_infraspecies->appendChild($this->_createFormatedText($dom, $infraspecies));
        $node_version->appendChild($this->_createFormatedText($dom, $version));
        
        $node_description->appendChild($this->_createFormatedText($dom, "Common names in CoL"));
        
        $current_date_time = gmdate("d M Y H:i:s T");
        $node_date->appendChild($this->_createFormatedText($dom, $current_date_time));
        
        $node_parameters->appendChild($node_genus);
        $node_parameters->appendChild($node_species);
        $node_parameters->appendChild($node_infraspecies);
        $node_parameters->appendChild($node_version);
        
        $node_service->appendChild($node_description);
        $node_service->appendChild($node_parameters);
        $node_service->appendChild($node_date);
        
        $node_request->appendChild($node_service);
        
        $node_sp2000->appendChild($node_request);
        
        try
        {            
            if ((!empty($genus)) && (!empty($key)) && !(empty($species) && !empty($infraspecies)))
            {            
                try
                {
                    $resource = Bootstrap::instance()->getPluginResource('multidb');
                    $this->_db = $resource->getDb('application');
                    
                    $sql = " SELECT COUNT(*) FROM key_store WHERE service_key = '" . $key . "' ";
                    $count = (int)$this->_db->fetchOne($sql);
                    
                    if ($count == 0)
                    {
                        $node_ws_status->setAttribute("id", "500");
                        $node_ws_status->appendChild($this->_createFormatedText($dom, "Invalid key."));
                        $continue = false;
                    }
                }
                catch (Exception $e)
                {
                    $node_ws_status->setAttribute("id", "400");
                    $node_ws_status->appendChild($this->_createFormatedText($dom, "Unable to verify the validity of the key."));
                    $continue = false;
                }
                
                if ($continue)
                {            
                    try
                    {
                        $resource = Bootstrap::instance()->getPluginResource('multidb');
                        $this->_db = $resource->getDb('baseschema');
                    }
                    catch (Exception $e)
                    {
                        $node_ws_status->setAttribute("id", "200");
                        $node_ws_status->appendChild($this->_createFormatedText($dom, "Unable to connect to the specified database."));
                        $continue = false;
                    }
                }
            
                if ($continue)
                {                
                    $node_response = $dom->createElement("response");
                    
                    $sql = " SELECT cne.id, cne.name, cn.language_iso, cn.country_iso
                            FROM _search_scientific ss
                                INNER JOIN common_name cn ON (ss.id = cn.taxon_id)
                                INNER JOIN common_name_element cne ON (cn.common_name_element_id = cne.id)
                            WHERE ss.genus = '" . $genus . "' ";
                    
                    if (!empty($species))
                    {
                        $sql .= " AND ss.species = '" . $species . "' ";
                    }
                    else
                    {
                        $sql .= " AND (ss.species IS NULL OR ss.species = '') ";
                    }
                    
                    if (!empty($infraspecies))
                    {
                        $sql .= " AND ss.infraspecies = '" . $infraspecies . "' ";
                    }
                    else
                    {
                        $sql .= " AND (ss.infraspecies IS NULL OR ss.infraspecies = '') ";
                    }
                    
                    $rows_commons = $this->_db->query($sql);
                    
                    while ($row_co = $rows_commons->fetch())
                    {
                        $found = true;
                        
                        $node_common = $dom->createElement("common");
                        $node_common->setAttribute("id", $row_co["id"]);
                        
                        $node_name = $dom->createElement("name");
                        $node_name->appendChild($this->_createFormatedText($dom, $row_co["name"]));
                        $node_language = $dom->createElement("language");
                        $node_language->appendChild($this->_createFormatedText($dom, $row_co["language_iso"]));
                        $node_country = $dom->createElement("country");
                        $node_country->appendChild($this->_createFormatedText($dom, $row_co["country_iso"]));
                        
                        $node_common->appendChild($node_name);
                        $node_common->appendChild($node_language);
                        $node_common->appendChild($node_country);
                        
                        $node_response->appendChild($node_common);
                    }
                    
                    if ($found)
                    {
                        $node_ws_status->setAttribute("id", "0");
                        $node_ws_status->appendChild($this->_createFormatedText($dom, "Success"));
                    }
                    else
                    {
                        $node_ws_status->setAttribute("id", "100");
                        $node_ws_status->appendChild($this->_createFormatedText($dom, "Common name not found"));
                    }
                    
                    $node_sp2000->appendChild($node_response);
                }
            }
            else
            {
                $node_ws_status->setAttribute("id", "300");
                $node_ws_status->appendChild($this->_createFormatedText($dom, "Required parameters not specified"));
            }
        }
        catch (Exception $e)
        {
            $node_ws_status->setAttribute("id", "600");
            $node_ws_status->appendChild($this->_createFormatedText($dom, $e->getMessage()));
        }
        
        $node_sp2000->appendChild($node_ws_status);
        $dom->appendChild($node_sp2000);
        
        return htmlspecialchars_decode($dom->saveXML(), ENT_NOQUOTES);
    }

    public function selectStatus ($genus, $species, $infraspecies, $version, $format, $key)
    {
        $found = false;
        $continue = true;
        
        $dom = new DOMDocument($this->_version, $this->_encoding);
        $dom->preserveWhiteSpace = $this->_preserveWhiteSpace;
        $dom->formatOutput = $this->_formatOutput;
        
        $node_ws_status = $dom->createElement("status");
        
        $node_sp2000 = $dom->createElement("sp2000");
        $node_request = $dom->createElement("request");
        $node_service = $dom->createElement("service");
        $node_description = $dom->createElement("description");
        $node_parameters = $dom->createElement("parameters");
        $node_genus = $dom->createElement("genus");
        $node_species = $dom->createElement("species");
        $node_infraspecies = $dom->createElement("infraspecies");
        $node_version = $dom->createElement("version");
        $node_date = $dom->createElement("date");
        
        $node_service->setAttribute("id", "status");
        
        $node_genus->appendChild($this->_createFormatedText($dom, $genus));
        $node_species->appendChild($this->_createFormatedText($dom, $species));
        $node_infraspecies->appendChild($this->_createFormatedText($dom, $infraspecies));
        $node_version->appendChild($this->_createFormatedText($dom, $version));
        
        $node_description->appendChild($this->_createFormatedText($dom, "Status of a scientific name in CoL"));
        
        $current_date_time = gmdate("d M Y H:i:s T");
        $node_date->appendChild($this->_createFormatedText($dom, $current_date_time));
        
        $node_parameters->appendChild($node_genus);
        $node_parameters->appendChild($node_species);
        $node_parameters->appendChild($node_infraspecies);
        $node_parameters->appendChild($node_version);
        
        $node_service->appendChild($node_description);
        $node_service->appendChild($node_parameters);
        $node_service->appendChild($node_date);
        
        $node_request->appendChild($node_service);
        
        $node_sp2000->appendChild($node_request);
        
        try
        {
            if ((!empty($genus)) && (!empty($key)) && !(empty($species) && !empty($infraspecies)))
            {                
                try
                {
                    $resource = Bootstrap::instance()->getPluginResource('multidb');
                    $this->_db = $resource->getDb('application');
                    
                    $sql = " SELECT COUNT(*) FROM key_store WHERE service_key = '" . $key . "' ";
                    $count = (int)$this->_db->fetchOne($sql);
                    
                    if ($count == 0)
                    {
                        $node_ws_status->setAttribute("id", "500");
                        $node_ws_status->appendChild($this->_createFormatedText($dom, "Invalid key."));
                        $continue = false;
                    }
                }
                catch (Exception $e)
                {
                    $node_ws_status->setAttribute("id", "400");
                    $node_ws_status->appendChild($this->_createFormatedText($dom, "Unable to verify the validity of the key."));
                    $continue = false;
                }
                
                if ($continue)
                {            
                    try
                    {
                        $resource = Bootstrap::instance()->getPluginResource('multidb');
                        $this->_db = $resource->getDb('baseschema');
                    }
                    catch (Exception $e)
                    {
                        $node_ws_status->setAttribute("id", "200");
                        $node_ws_status->appendChild($this->_createFormatedText($dom, "Unable to connect to the specified database."));
                        $continue = false;
                    }
                }
            
                if ($continue)
                {                
                    $node_response = $dom->createElement("response");
                    
                    $sql = " SELECT sns.name_status, ssac.id, ssac.kingdom, ssac.phylum, ssac.class, ssac.`order`, ssac.superfamily, ssac.family, ssac.genus, 
                                    ssac.subgenus, ssac.species, ssac.infraspecific_marker, ssac.infraspecies, ssac.author
                             FROM _search_scientific ss
                                LEFT JOIN scientific_name_status sns ON (ss.status = sns.id)
                                LEFT JOIN _search_scientific ssac ON (ss.accepted_species_id = ssac.id OR (ss.status IN (0,1,4) AND ss.id = ssac.id))
                             WHERE ss.genus = '" . $genus . "' ";
                    
                    if (!empty($species))
                    {
                        $sql .= " AND ss.species = '" . $species . "' ";
                    }
                    else
                    {
                        $sql .= " AND (ss.species IS NULL OR ss.species = '') ";
                    }
                    
                    if (!empty($infraspecies))
                    {
                        $sql .= " AND ss.infraspecies = '" . $infraspecies . "' ";
                    }
                    else
                    {
                        $sql .= " AND (ss.infraspecies IS NULL OR ss.infraspecies = '') ";
                    }
                    
                    $rows_sciname = $this->_db->query($sql);
                    
                    $first = true;
                    
                    while ($row_sc = $rows_sciname->fetch())
                    {
                        $found = true;
                        
                        if ($first)
                        {
                            $node_status = $dom->createElement("status");
                            $node_status->appendChild($this->_createFormatedText($dom, $row_sc["name_status"]));
                            $node_response->appendChild($node_status);
                            $first = false;
                        }
                        
                        if (!empty($row_sc["id"]))
                        {
                            $node_accepted = $dom->createElement("accepted_name");
                            $node_accepted->setAttribute("id", $row_sc["id"]);
                            
                            $node_kingdom = $dom->createElement("kingdom");
                            $node_kingdom->appendChild($this->_createFormatedText($dom, $row_sc["kingdom"]));
                            $node_phylum = $dom->createElement("phylum");
                            $node_phylum->appendChild($this->_createFormatedText($dom, $row_sc["phylum"]));
                            $node_class = $dom->createElement("class");
                            $node_class->appendChild($this->_createFormatedText($dom, $row_sc["class"]));
                            $node_order = $dom->createElement("order");
                            $node_order->appendChild($this->_createFormatedText($dom, $row_sc["order"]));
                            $node_superfamily = $dom->createElement("superfamily");
                            $node_superfamily->appendChild($this->_createFormatedText($dom, $row_sc["superfamily"]));
                            $node_family = $dom->createElement("family");
                            $node_family->appendChild($this->_createFormatedText($dom, $row_sc["family"]));
                            $node_genus = $dom->createElement("genus");
                            $node_genus->appendChild($this->_createFormatedText($dom, $row_sc["genus"]));
                            $node_subgenus = $dom->createElement("subgenus");
                            $node_subgenus->appendChild($this->_createFormatedText($dom, $row_sc["subgenus"]));
                            $node_species = $dom->createElement("species");
                            $node_species->appendChild($this->_createFormatedText($dom, $row_sc["species"]));
                            $node_infraspecific_marker = $dom->createElement("infraspecific_marker");
                            $node_infraspecific_marker->appendChild(
                            $this->_createFormatedText($dom, $row_sc["infraspecific_marker"]));
                            $node_infraspecies = $dom->createElement("infraspecies");
                            $node_infraspecies->appendChild($this->_createFormatedText($dom, $row_sc["infraspecies"]));
                            $node_author = $dom->createElement("author");
                            $node_author->appendChild($this->_createFormatedText($dom, $row_sc["author"]));
                            
                            $node_accepted->appendChild($node_kingdom);
                            $node_accepted->appendChild($node_phylum);
                            $node_accepted->appendChild($node_class);
                            $node_accepted->appendChild($node_order);
                            $node_accepted->appendChild($node_superfamily);
                            $node_accepted->appendChild($node_family);
                            $node_accepted->appendChild($node_genus);
                            $node_accepted->appendChild($node_subgenus);
                            $node_accepted->appendChild($node_species);
                            $node_accepted->appendChild($node_infraspecific_marker);
                            $node_accepted->appendChild($node_infraspecies);
                            $node_accepted->appendChild($node_author);
                            
                            $node_response->appendChild($node_accepted);
                        }
                    }
                    
                    if ($found)
                    {
                        $node_ws_status->setAttribute("id", "0");
                        $node_ws_status->appendChild($this->_createFormatedText($dom, "Success"));
                    }
                    else
                    {
                        $node_ws_status->setAttribute("id", "100");
                        $node_ws_status->appendChild($this->_createFormatedText($dom, "Name not found"));
                    }
                    
                    $node_sp2000->appendChild($node_response);
                }
            }
            else
            {
                $node_ws_status->setAttribute("id", "300");
                $node_ws_status->appendChild($this->_createFormatedText($dom, "Required parameters not specified"));
            }
        }
        catch (Exception $e)
        {
            $node_ws_status->setAttribute("id", "600");
            $node_ws_status->appendChild($this->_createFormatedText($dom, $e->getMessage()));
        }
        
        $node_sp2000->appendChild($node_ws_status);
        $dom->appendChild($node_sp2000);
        
        return htmlspecialchars_decode($dom->saveXML(), ENT_NOQUOTES);
    }

    protected function _createFormatedText ($dom, $value)
    {
        return $this->_isValidXml($value) ? $dom->createTextNode($value) : $dom->createCDATASection($value);
    }

    protected function _isValidXml ($str)
    {
        return @simplexml_load_string('<x>' . $str . '</x>') instanceof SimpleXMLElement;
    }
}