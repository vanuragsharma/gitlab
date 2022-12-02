<?php

namespace BoostMyShop\Supplier\Model\Order;

use mysql_xdevapi\Exception;

class FtpUpload
{
    protected $_config;
    protected $_fileExport;
    protected $_fileFactory;
    protected $sftp;
    protected $ftp;

    public function __construct(
        \BoostMyShop\Supplier\Model\Config $config,
        \BoostMyShop\Supplier\Model\Order\FileExport $fileExport,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem\Io\Sftp $sftp,
        \Magento\Framework\Filesystem\Io\Ftp $ftp
    )
    {
        $this->_config = $config;
        $this->_fileExport = $fileExport;
        $this->_fileFactory = $fileFactory;
        $this->sftp = $sftp;
        $this->ftp = $ftp;
    }

    public function process($purchaseOrder)
    {
        try
        {
            $supplier = $purchaseOrder->getSupplier();
            $content = $this->_fileExport->getFileContent($purchaseOrder);
            $fileName = $this->getFileName($purchaseOrder);
            $this->createConnection($supplier);
            $this->connection->write($fileName, $content);
            $this->connection->close();

            // adding history
            $purchaseOrder->addHistory('File uploaded on FTP : '.$fileName);

        }
        catch(\Exception $ex)
        {
            $purchaseOrder->addHistory('Error during FTP upload : '.$ex->getMessage());
            throw new \Exception(__('An error occured during FTP upload : %1', $ex->getMessage()));
        }
    }

    public function createConnection($supplier)
    {
        if (!$supplier->getsup_notif_ftp_host()|| !$supplier->getsup_notif_ftp_port() || !$supplier->getsup_notif_ftp_login() || !$supplier->getsup_notif_ftp_password())
            throw new \InvalidArgumentException('Required config not found.');

        if($supplier->getsup_notif_ftp_sftp() == 1) {
            $this->connection = new \Magento\Framework\Filesystem\Io\Sftp();
            $this->connection->open(
                ['host' => $supplier->getsup_notif_ftp_host() . ":" . $supplier->getsup_notif_ftp_port(), 'username' => $supplier->getsup_notif_ftp_login(), 'password' => $supplier->getsup_notif_ftp_password()]
            );
        }else{
            $this->connection = new \Magento\Framework\Filesystem\Io\Ftp();
            $this->connection->open(
                ['passive' => $supplier->getsup_notif_ftp_passive(), 'host' => $supplier->getsup_notif_ftp_host() , "port" . $supplier->getsup_notif_ftp_port(), 'user' => $supplier->getsup_notif_ftp_login(), 'password' => $supplier->getsup_notif_ftp_password()]
            );
        }
        if($supplier->getsup_notif_ftp_directory())
            $this->connection->cd($supplier->getsup_notif_ftp_directory());

        return $this;
    }

    public function getFileName($purchaseOrder)
    {
        $fileName = '';
        $supplier = $purchaseOrder->getSupplier();
        if($supplier->getsup_notif_ftp_file_name()) {
            $fileName = $supplier->getsup_notif_ftp_file_name();
            if(strpos($fileName, '{reference}') !== false)
                $fileName = str_replace('{reference}', $purchaseOrder->getpo_reference(), $fileName);
            if(strpos($fileName, '{d}') !== false)
                $fileName = str_replace('{d}', date('d'), $fileName);
            if(strpos($fileName, '{m}') !== false)
                $fileName = str_replace('{m}', date('m'), $fileName);
            if(strpos($fileName, '{Y}') !== false)
                $fileName = str_replace('{Y}', date('Y'), $fileName);
        }else
            $fileName = $this->_fileExport->getFileName($purchaseOrder);

        return $fileName;
    }
}