<?php
namespace Magecomp\Emailquotepro\Api;
interface PostManagementInterface {


    /**
     * GET for Post api
     * @param string $customername
     * @param string $customeremail
     * @param string $telephone
     * @param string $comment
     * @param string $quoteid
     * @return string
     */

    public function sendMail($customername,$customeremail,$telephone,$comment,$quoteid);

    /**
     * DELETE for Post api
     * @param string $quoteid
     * @return string
     */

    public function deleteQuote($quoteid);

     /**
     * GET for Post api
     * @param string $quoteid
     * @return string
     */

    public function getSpecificQuote($quoteid);

     /**
     * GET for Post api
     * @return string
     */

    public function getAllQuote();

    /**
     * GET for Post api
     * @param string $customername
     * @param string $customeremail
     * @param string $telephone
     * @param string $comment
     * @param string $emailquoteid
     * @return string
     */

    public function getUpdateQuote($customername,$customeremail,$telephone,$comment,$emailquoteid);


}