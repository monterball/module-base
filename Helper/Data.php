<?php
/**
 * Module base of Eloab package
 * @package Eloab_Base
 * @author Bao Le
 * @date 2022
 * @description Helper for all the Eloab package modules
 */
namespace Eloab\Base\Helper;

use Eloab\Base\Model\Logger;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var DirectoryList */
    protected $directoryList;

    /** @var File  */
    protected $ioFile;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DirectoryList $directoryList
     * @param File $ioFile
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DirectoryList $directoryList,
        File $ioFile
    ) {
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->ioFile = $ioFile;

        parent::__construct($context);
    }

    /**
     * Create folder and permissions
     * @param string $path
     * @param int $mod should be 0777
     * @param string $delimiter
     */
    public function mkdirChmod($path, $mod, $delimiter = '/var/') : void
    {
        $baseDir = BP . "/" . trim($delimiter, '/') . "/";
        $arrPath = explode($delimiter, $path);
        $arrPath = $arrPath[count($arrPath) - 1];
        $arrPath = explode("/", $arrPath);
        for ($i = 0; $i < count($arrPath) - 1; $i++) {
            $baseDir .= $arrPath[$i] . "/";
            if (!file_exists($baseDir)) {
                @mkdir($baseDir, $mod);
                @chmod($baseDir, $mod);
            }
        }
    }

    public function getLogger() : LoggerInterface
    {
        return $this->_logger;
    }

    public function setLogger(Logger $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * Create new logger
     * @param $logPath
     * @param $logFileName
     * @param bool $inShell
     * @return Logger
     * @throws \Exception
     */
    public function createLogger($logPath, $logFileName, $inShell = false) : Logger
    {
        $this->ioFile->setAllowCreateFolders(true);
        $this->ioFile->checkAndCreateFolder($logPath);
        $logFilePath = rtrim($logPath, '/') . '/' . $logFileName;
        $writer = new \Zend\Log\Writer\Stream($logFilePath);
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->setInShell($inShell);
        return $logger;
    }

    /**
     * @param $fileName
     * @return bool
     */
    public function createFlagFile($fileName) : bool
    {
        $path = BP . "/" . 'var' . "/" . 'log' . "/" . $fileName;
        $handle = @fopen($path, "w");
        @fclose($handle);

        if (file_exists($path)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getRealIpAddr() : ?string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //check ip from share internet
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //to check ip is pass from proxy
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * @param $productId
     * @param null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductImage($productId, $storeId = null) : string
    {
        $product = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\Product::class)
            ->load($productId);
        //Set store ID
        if ($storeId) {
            $product->setStoreId($storeId);
        }
        $imageUrlDefault = $this
            ->storeManager
            ->getStore()
            ->getConfig('catalog/placeholder/thumbnail_placeholder');
        return $product->getImage() ? $product->getImage() : '/placeholder/'. $imageUrlDefault;
    }

    /**
     * @param $productId
     * @param null $storeId
     * @return int
     */
    public function getProductRating($productId, $storeId = null) : int
    {
        $summary = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Review\Model\Rating::class)
            ->getEntitySummary($productId, false);
        $averageRating = 0;
        foreach ($summary as $item) {
            $row = $item->getData();
            if (isset($row['store_id']) && (int)$row['store_id'] == $storeId) {
                if ($row['count'] > 0) {
                    $averageRating = (int) number_format(
                        ((int) $row['sum'] / (int) $row['count'])*5/100,
                        0
                    );
                } else {
                    $averageRating = 0;
                }

            }
        }

        return $averageRating;
    }
}
