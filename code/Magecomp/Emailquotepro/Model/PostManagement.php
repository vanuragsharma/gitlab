<?php
namespace Magecomp\Emailquotepro\Model;
use Magecomp\Emailquotepro\Api\PostManagementInterface;
use Magecomp\Emailquotepro\Helper\Apicall;

class PostManagement implements PostManagementInterface
{
    protected $apicallHelper;

    public function __construct(
        Apicall $apicallHelper
    )
    {
        $this->apicallHelper = $apicallHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMail($customername,$customeremail,$telephone,$comment,$quoteid)
    {
        try{
                $response=$this->apicallHelper->sendEmail($customername,$customeremail,$telephone,$comment,$quoteid);
                 $response = [
                    'status' => $response,
                 ];

        }catch(\Exception $e) {
            $response=['error' => $e->getMessage()];
        }
        return json_encode($response);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQuote($quoteid)
    {
       try{
            $return=false;
            $return=$this->apicallHelper->deleteQuote($quoteid);
            $response = [
                    'status' => $return,
            ];

        }catch(\Exception $e) {
            $response=['error' => $e->getMessage()];
        }
        return json_encode($response);
    }

     /**
     * {@inheritdoc}
     */
    public function getSpecificQuote($quoteid)
    {
       try{
            $return=null;
            $return=$this->apicallHelper->getSpecificQuote($quoteid);
            $response = [
                    'status' => $return,
            ];

        }catch(\Exception $e) {
            $response=['error' => $e->getMessage()];
        }
        return json_encode($response);
    }
     /**
     * {@inheritdoc}
     */
    public function getAllQuote()
    {
       try{
            $return=null;
            $return=$this->apicallHelper->getAllQuote();
            $response = [
                    'data' => $return,
            ];

        }catch(\Exception $e) {
            $response=['return' => $e->getMessage()];
        }
        return json_encode($response);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getUpdateQuote($customername,$customeremail,$telephone,$comment,$emailquoteid)
    {
       try{
            $return=null;
            $return=$this->apicallHelper->getUpdateQuote($customername,$customeremail,$telephone,$comment,$emailquoteid);
        
            $response = [
                    'status' => $return,
            ];

        }catch(\Exception $e) {
            $response=['return' => $e->getMessage()];
        }
        return json_encode($response);
    } 

}