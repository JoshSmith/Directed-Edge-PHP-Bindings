<?php

require_once('config.php');

class DirectedEdgeRest
{
  /**
   * Adds an item with a tag
   * @param string $item_id Item ID
   * @param string $tag_name Tag Name
   *
   * @return bool True if add succeeded, false otherwise
   */
  public function addItemWithTag($item_id, $tag_name)
  {
    $xml = $this->XMLForItemWithTag($item_id, $tag_name);
    if($this->addNewItem($item_id, $xml)) {
      return true;
    }
  }
  
  /**
   * Adds an item with a link
   * @param string $item_id Item ID
   * @param string $link_name Link Name
   *
   * @return bool True if add succeeded, false otherwise
   */
  public function addItemWithLink($item_id, $link_name)
  {
    $xml = $this->XMLForItemWithLink($item_id, $link_name);
    if($this->addNewItem($item_id, $xml)) {
      return true;
    }
  }
  
  /**
   * Adds an item with a link and weight
   * @param string $item_id Item ID
   * @param string $link_name Link Name
   * @param int $weight Weight for link, from 1 to 10
   *
   * @return bool True if add succeeded, false otherwise
   */
  public function addItemWithLinkAndWeight($item_id, $link_name, $weight)
  {
    $xml = $this->XMLForItemWithLinkAndWeight($item_id, $link_name, $weight);
    if($this->addNewItem($item_id, $xml)) {
      return true;
    }
  }

  /**
   * Adds an item with a link and weight
   * @param string $item_id Item ID
   * @param string $link_name Link Name
   * @param int $weight Weight for link, from 1 to 10
   * @param string $type Type of link
   *
   * @return bool True if add succeeded, false otherwise
   */
  public function addItemWithLinkAndWeightOfType($item_id, $link_name, $weight, $type)
  {
    $xml = $this->XMLForItemWithLinkAndWeightOfType($item_id, $link_name, $weight, $type);
    if($this->addNewItem($item_id, $xml)) {
      return true;
    }
  }
  
  /**
   * Updates an item with a tag
   * @param string $item_id Item ID
   * @param string $tag_name Tag Name
   *
   * @return bool True if add succeeded, false otherwise
   */
  public function updateItemWithTag($item_id, $tag_name)
  {
    $xml = $this->XMLForItemWithTag($item_id, $tag_name);
    if($this->updateItemAdd($item_id, $xml)) {
      return true;
    }  
  }

  /**
   * Updates an item with a link
   * @param string $item_id Item ID
   * @param string $link_name Link Name
   *
   * @return bool True if add succeeded, false otherwise
   */
  public function updateItemWithLink($item_id, $link_name)
  {
    $xml = $this->XMLForItemWithLink($item_id, $link_name);
    if($this->updateItemAdd($item_id, $xml)) {
      return true;
    }  
  }

  /**
   * Updates an item with a link and weight
   * @param string $item_id Item ID
   * @param string $link_name Link Name
   * @param int $weight Weight for item, from 1 to 10
   *
   * @return bool True if add succeeded, false otherwise
   */
  public function updateItemWithLinkAndWeight($item_id, $link_name, $weight)
  {
    $xml = $this->XMLForItemWithLinkAndWeight($item_id, $link_name, $weight);
    if($this->updateItemAdd($item_id, $xml)) {
      return true;
    }
  }

  /**
   * Updates an item with a link and weight
   * @param string $item_id Item ID
   * @param string $link_name Link Name
   * @param int $weight Weight for item, from 1 to 10
   * @param string $type Type of link
   *
   * @return bool True if add succeeded, false otherwise
   */
  public function updateItemWithLinkAndWeightOfType($item_id, $link_name, $weight, $type)
  {
    $xml = $this->XMLForItemWithLinkAndWeightOfType($item_id, $link_name, $weight, $type);
    if($this->updateItemAdd($item_id, $xml)) {
      return true;
    }
  }

  /**
   * Removes a tag from an item
   * @param string $item_id Item ID
   * @param string $tag_name Tag Name
   *
   * @return bool True if removal succeeded, false otherwise
   */
  public function removeTagFromItem($item_id, $tag_name)
  {
    $xml = $this->XMLForItemWithTag($item_id, $tag_name);
    if($this->updateItemRemove($item_id, $xml)) {
      return true;
    }
  }
    
  /**
   * Removes a link from an item
   * @param string $item_id Item ID
   * @param string $link_name Link Name
   *
   * @return bool True if removal succeeded, false otherwise
   */
  public function removeLinkFromItem($item_id, $link_name)
  {
    $xml = $this->XMLForItemWithLink($item_id, $link_name);
    if($this->updateItemRemove($item_id, $xml)) {
      return true;
    }
  }
  
  /**
   * Deletes an item entirely
   * @param string $item_id Item ID
   *
   * @return bool True if deletion succeeded, false otherwise
   */
  public function deleteItem($item_id)
  {
    if($this->removeItem($item_id)) {
      return true;
    }
  }
    
  /**
   * Returns array of recommended result IDs for an item
   * @param string $item Item, e.g. "Miles%20Davis"
   * @param string $tags Tags as comma delimited string, e.g. "product,page"
   * @param int $limit Limit for max results
   *
   * @return array Recommended result IDs
   */
  public function getRecommended($item, $tags, $limit)
  {
    // Connect to Directed Edge and parse the returned XML
    $targeturl = $this->buildURL($item, 'recommended', $tags, $limit, 'true');
    $response = $this->getCurlResponse($targeturl);
    $xml = $this->parseXML($response);
    
    // Iterate through the XML and place IDs into an array
    foreach($xml->item->recommended as $recommended) {
      $recommendedResults[] = filter_var($recommended, FILTER_SANITIZE_NUMBER_INT);
    }
    return $recommendedResults;
  }
  
  /**
   * Gets multiple simultaneous recommendations from Directed Edge
   * @param array $queryArray Array of the form array(0 => (array('item' => (string) $item, 'tags' => (string) $tags, 'limit' => (int) $limit))
   *
   * @return array Multi-dimensional array containing responses to
   *  queries in the order they were passed in the array
   */
  public function getMultiRecommended($queryArray)
  {
    $targetUrls = array();
  
    foreach($queryArray as $query) {
      $targeturl = $this->buildURL($query['item'], 'recommended', $query['tags'], $query['limit'], 'true');
      $targetUrls[] = $targeturl;
    }
    
    $responses = $this->getMultiCurlResponses($targetUrls);
    
    $xmlArray = array();
    
    foreach($responses as $response) {
      $xmlArray[] = $this->parseXML($response);      
    }

    $count =  count($xmlArray);
    
    // Iterate through the XML and place IDs into an array
    for($i = 0; $i < $count; $i++) {            
      foreach($xmlArray[$i]->item->recommended as $recommended) {
        $recommendedResults[$i][] = filter_var($recommended, FILTER_SANITIZE_NUMBER_INT);
      }
    }
    
    return $recommendedResults;
  }

  /**
   * Returns array of related result IDs for an item
   * @param string $item Item, e.g. "Miles%20Davis"
   * @param string $tags Tags as comma delimited string, e.g. "product,page"
   * @param int $limit Limit for max results
   *
   * @return array Recommended result IDs
   */
  public function getRelated($item, $tags, $limit)
  {
    // Connect to Directed Edge and parse the returned XML
    $targeturl = $this->buildURL($item, 'related', $tags, $limit, 'false');
    $response = $this->getCurlResponse($targeturl);
    $xml = $this->parseXML($response);

    // Iterate through the XML and place IDs into an array
    foreach($xml->item->related as $related) {
      $relatedResults[] = filter_var($related, FILTER_SANITIZE_NUMBER_INT);
    }
    return $relatedResults;
  }
    
  /**
   * Returns array of related result IDs for an item, excluding links that already exist
   * This is what you'd use for a friend finder
   * @param string $item Item, e.g. "Miles%20Davis"
   * @param string $tags Tags as comma delimited string, e.g. "product,page"
   * @param int $limit Limit for max results
   *
   * @return array Recommended result IDs
   */
  public function getRelatedExcludeLinked($item, $tags, $limit)
  {
    // Connect to Directed Edge and parse the returned XML
    $targeturl = $this->buildURL($item, 'related', $tags, $limit, 'true');
    $response = $this->getCurlResponse($targeturl);
    $xml = $this->parseXML($response);

    // Iterate through the XML and place IDs into an array
    foreach($xml->item->related as $related) {
      $relatedResults[] = filter_var($related, FILTER_SANITIZE_NUMBER_INT);
    }
    return $relatedResults;
  }
    
  /**
   * Builds URL for cURL
   * @param string $item Item, e.g. "Miles%20Davis"
   * @param string $type Type of API request: either "related" or "recommended"
   * @param string $tags Tags as comma delimited string, e.g. "product,page"
   * @param int $limit Limit for max results
   * @param string $exclude "true" if you want to exclude linked, "false" otherwise
   *
   * @return string The target URL
   */
  private function buildURL($item, $type, $tags, $limit, $exclude)
  {
    $targeturl = DE_BASE_URL;
    $targeturl .= $item; // Item
    $targeturl .= "/" . $type; // Type
    $targeturl .= "?tags=" . $tags; // Tags
    $targeturl .= "&maxresults=" . $limit; // Limit
    $targeturl .= "&excludeLinked=" . $exclude; // Exclude
    return $targeturl;
  }
  
  /**
   * Builds the URL for adding to Directed Edge
   * @param string $item_id Item ID
   *
   * @return string URL
   */
  private function buildAddURL($item_id)
  {
    $targeturl = DE_BASE_URL;
    $targeturl .= $item_id; // Item
    return $targeturl;
  }

  /**
   * Builds the URL for updating Directed Edge
   * @param string $item_id Item ID
   * @param string $pseudoResource Either "add" or "remove"
   *
   * @return string URL
   */
  private function buildUpdateURL($item_id, $pseudoResource)
  {
    $targeturl = DE_BASE_URL;
    $targeturl .= $item_id; // Item
    $targeturl .= "/" . $pseudoResource; // Pseudo-Resource
    return $targeturl;
  }

  /**
   * Builds the URL for removing frm Directed Edge
   * @param string $item_id Item ID
   *
   * @return string URL
   */
  private function buildRemoveURL($item_id)
  {
    $targeturl = DE_BASE_URL;
    $targeturl .= $item_id; // Item
    $targeturl .= "/remove"; // Pseudo-Resource
    return $targeturl;
  }

  /**
   * Returns the cURL response given a target URL
   * @param string $targeturl The target URL for cURL
   *
   * @return string cURL Response
   */
  private function getCurlResponse($targeturl)
  {
    $ch = curl_init($targeturl);
    curl_setopt($ch, CURLOPT_POST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
  }

  /**
   * Returns the cURL responses given multiple target URLs
   * @param array $targetUrls Array of target URLs for cURL
   *
   * @return array cURL Responses
   */
  private function getMultiCurlResponses($targetUrls)
  {
    // Cache the count
    $count = count($targetUrls);
  
    // Create the multiple cURL handles
    for($i = 0; $i < $count; $i++) {
      $ch[$i] = curl_init($targetUrls[$i]);
      curl_setopt($ch[$i], CURLOPT_POST, FALSE);
      curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, TRUE);
    }

    // Initialize the multiple cURL handle
    $mh = curl_multi_init();
    
    // Add the handles to the curl_multi handle
    for($i = 0; $i < $count; $i++) {
      curl_multi_add_handle($mh, $ch[$i]);
    }
    
    $running = null;
    // Execute the handles
    do {
      curl_multi_exec($mh, $running);
    } while ($running > 0);

    $responses = array();

    // Remove the handles and return the response
    for($i = 0; $i < $count; $i++) {
      curl_multi_remove_handle($mh, $ch[$i]);
      
      $responses[$i] = curl_multi_getcontent($ch[$i]);
    }

    // Close the multiple cURL handle
    curl_multi_close($mh);
    
    return $responses;
  }

  /**
   * Imports using cURL
   * @param string $targeturl The target URL for cURL
   * @param string $xml XML to be imported
   *
   * @return bool True if import failed, false otherwise
   */
  private function importCurlXML($targeturl, $xml)
  {
    $header[] = "Content-type: text/xml";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $targeturl); 
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_TIMEOUT, 900); 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
    curl_setopt($ch, CURLOPT_FAILONERROR, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    if(curl_exec($ch)) {
      $result = true;
    }
    curl_close($ch);
    return $result;
  }
  
  /**
   * Deletes using cURL
   * @param string $targeturl The target URL for cURL
   *
   * @return bool True if delete failed, false otherwise
   */
  private function deleteCurl($targeturl)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_URL, $targeturl); 
    curl_setopt($ch, CURLOPT_FAILONERROR, false); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    if(curl_exec($ch)) {
      $result = true;
    }
    curl_close($ch);
    return $result;
  }

  /**
   * Parses returned XML
   * @param string $response Response string from cURL
   *
   * @return string XML, parsed 
   */  
  private function parseXML($response)
  {
    $xml = simplexml_load_string($response);
    return $xml;
  }
  
  /**
   * Creates new DOM Document for Directed Edge
   * @return resource DOMDocument()
   */
  private function createNewDOMDocument()
  {
    $this->doc = new DOMDocument("1.0", "UTF-8"); 
    $this->doc->formatOutput = true; 
     
    $this->root = $this->doc->createElement('directedege');
    $this->doc->appendChild($this->root);
  
    $attr = $this->doc->createAttribute("version"); 
    $this->root->appendChild($attr);
   
    $attr->appendChild($this->doc->createTextNode("0.1"));
  }
  
  /**
   * Creates the Item Element and returns the resource
   * @param $item_id
   *
   * @return resource Item Element
   */
  private function createItemElement($item_id)
  {
    $item = $this->doc->createElement("item");

    $attr = $this->doc->createAttribute("id"); 
    $item->appendChild($attr);
    
    $attr->appendChild($this->doc->createTextNode($item_id));
    
    return $item;
  }

  /**
   * Generates XML for an item with a tag
   * @param string $item_id Item ID
   * @param string $tag_name Tag Name
   *
   * @return string XML
   */
  private function XMLForItemWithTag($item_id, $tag_name)
  {
    $this->createNewDOMDocument();
    $item = $this->createItemElement($item_id);
    
    $tag = $this->doc->createElement("tag"); 
    $tag->appendChild($this->doc->createTextNode($tag_name)); 
    $item->appendChild($tag);
    
    $this->root->appendChild($item);

    $xml = $this->doc->saveXML();
    return $xml;
  }

  /**
   * Generates XML for an item with a link
   * @param string $item_id Item ID
   * @param string $link_name Link Name
   *
   * @return string XML
   */
  private function XMLForItemWithLink($item_id, $link_name)
  {
    $this->createNewDOMDocument();
    $item = $this->createItemElement($item_id);

    $link = $this->doc->createElement("link");
    $link->appendChild($this->doc->createTextNode($link_name));
    $root = $item->appendChild($link);

    $this->root->appendChild($item);

    $xml = $this->doc->saveXML();
    return $xml;
  }

  /**
   * Generates XML for an item with a link and weight
   * @param string $item_id Item ID
   * @param string $link_name Link Name
   * @param int $weight Weight, any number from 1 to 10
   *
   * @return string XML
   */  
  private function XMLForItemWithLinkAndWeight($item_id, $link_name, $weight)
  {
    $this->createNewDOMDocument();
    $item = $this->createItemElement($item_id);

    $link = $this->doc->createElement("link");
    $link->appendChild($this->doc->createTextNode($link_name));
    $root = $item->appendChild($link);
    
    // Create the "weight" attribute
    $attr = $link->appendChild($this->doc->createAttribute('weight'));    
    $link->appendChild($attr);
    
    // Set the weight value
    $attr->appendChild($this->doc->createTextNode($weight));

    $this->root->appendChild($item);

    $xml = $this->doc->saveXML();
    return $xml;
  }

  /**
   * Generates XML for an item with a link and weight
   * @param string $item_id Item ID
   * @param string $link_name Link Name
   * @param int $weight Weight, any number from 1 to 10
   * @param string $type Type of link
   *
   * @return string XML
   */  
  private function XMLForItemWithLinkAndWeightOfType($item_id, $link_name, $weight, $type)
  {
    $this->createNewDOMDocument();
    $item = $this->createItemElement($item_id);

    $link = $this->doc->createElement("link");
    $link->appendChild($this->doc->createTextNode($link_name));
    $root = $item->appendChild($link);
    
    // Create the "weight" attribute
    $attr = $link->appendChild($this->doc->createAttribute('weight'));    
    $link->appendChild($attr);
    
    // Set the weight value
    $attr->appendChild($this->doc->createTextNode($weight));

    // Create the "weight" attribute
    $attr2 = $link->appendChild($this->doc->createAttribute('type'));    
    $link->appendChild($attr2);
    
    // Set the weight value
    $attr2->appendChild($this->doc->createTextNode($type));

    $this->root->appendChild($item);

    $xml = $this->doc->saveXML();
    return $xml;
  }

  /**
   * Generates XML for removing a link
   * @param string $item_id Item ID
   * @param string $link_name Link Name
   *
   * @return string XML
   */
  private function XMLForRemoveLink($item_id, $link_name)
  {
    $this->createNewDOMDocument();
    $item = $this->createItemElement($item_id);

    $link = $this->doc->createElement("link");
    $link->appendChild($this->doc->createTextNode($link_name));
    $root = $item->appendChild($link);

    $this->root->appendChild($item);

    $xml = $this->doc->saveXML();
    return $xml;
  }
  
  /**
   * Adds a new item to Directed Edge with cURL
   * @param string $item_id Item ID
   * @param string $xml XML
   *
   * @return bool True if update succeeded, false otherwise
   */
  private function addNewItem($item_id, $xml)
  {
    $targeturl = $this->buildAddURL($item_id);  
    if($this->importCurlXML($targeturl, $xml)) {
      return true;
    }
  }
  
  /**
   * Adds to an item
   * @param string $item_id Item ID
   * @param string $xml XML
   *
   * @return bool True if update succeeded, false otherwise
   */
  private function updateItemAdd($item_id, $xml)
  {
    $targeturl = $this->buildUpdateURL($item_id, 'add');  
    if($this->importCurlXML($targeturl, $xml)) {
      return true;
    }
  }
  
  /**
   * Removes from an item
   * @param string $item_id Item ID
   * @param string $xml XML
   *
   * @return bool True if update succeeded, false otherwise
   */
  private function updateItemRemove($item_id, $xml)
  {
    $targeturl = $this->buildUpdateURL($item_id, 'remove');  
    if($this->importCurlXML($targeturl, $xml)) {
      return true;
    }
  }

  /**
   * Removes an item entirely
   * @param string $item_id Item ID
   *
   * @return bool True if removal succeeded, false otherwise
   */
  private function removeItem($item_id)
  {
    $targeturl = $this->buildRemoveURL($item_id);  
    if($this->deleteCurl($targeturl)) {
      return true;
    }
  }
}