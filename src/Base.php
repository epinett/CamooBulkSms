<?php

class Base {

    const DS = '/';
    protected $sEndPoint = 'https://api.camoo.cm';
    
     /**
     * @var string The resource name as it is known at the server
     */
    protected $resourceName = NULL;

    /**
     * @param $resourceName
     */
    public function setResourceName($resourceName)
    {
        $this->resourceName = $resourceName;
    }
    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }
        
      /**
      * Target version for "Classic" Camoo API
      */
     protected $camooClassicApiVersion = 'v1';


     /**
      * Returns the CAMOO API URL
      *-
      * @return string
      * @author Epiphane Tchabom 
      **/
     public function getEndPointUrl() {
         $sUrlTmp = $this->sEndPoint.static::DS.$this->camooClassicApiVersion.static::DS;
         $sResource = '';
         if ( $this->getResourceName() !== NULL && $this->getResourceName() !== 'sms' ) {
         	$sResource = static::DS.$this->getResourceName();
         }
         return sprintf($sUrlTmp.'sms'.$sResource.'%s','.json');
     }
}
