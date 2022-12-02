<?php
/**
 * Copyright ©  MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\Info\Model;

use \Magento\Framework\App\Filesystem\DirectoryList;

class MetaPackageList
{
    const VENDOR = 'MageWorx';

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $dir;

    /**
     * @var array
     */
    protected $packages;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformation;

    /**
     * MetaPackageList constructor.
     *
     * @param DirectoryList $dir
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Composer\ComposerInformation $composerInformation
     */
    public function __construct(
        DirectoryList $dir,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Composer\ComposerInformation $composerInformation
    ) {
        $this->dir                 = $dir;
        $this->filesystem          = $filesystem;
        $this->readFactory         = $readFactory;
        $this->composerInformation = $composerInformation;
    }

    /**
     * @return array|null
     */
    public function getInstalledExtensionList()
    {
        if ($this->packages === null) {
            try {
                $this->packages = array_merge($this->readLocalCodePath(), $this->readVendorPath());
            } catch (\Magento\Framework\Exception\FileSystemException $e) {
                return $this->packages = [];
            }
        }

        return $this->packages;
    }

    /**
     * @return array
     */
    public function getInstalledExtensionCodes()
    {
        $list = $this->getInstalledExtensionList();

        return array_keys($list);
    }


    /**
     * @param string $metaPackageName
     * @return string
     */
    public function getInstalledVersion($metaPackageName)
    {
        $list = $this->getInstalledExtensionList();

        if (isset($list[$metaPackageName]['version'])) {
            return $list[$metaPackageName]['version'];
        }

        return '';
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function readLocalCodePath()
    {
        $result = [];

        $path = $this->dir->getPath(DirectoryList::APP) . DIRECTORY_SEPARATOR . 'code' .
            DIRECTORY_SEPARATOR . self::VENDOR . DIRECTORY_SEPARATOR;

        $directoryRead = $this->readFactory->create($path);

        if (!$directoryRead->isDirectory($path)) {
            return $result;
        }

        try {
            $directories = $directoryRead->read();
            foreach ($directories as $directory) {
                if ($directoryRead->isDirectory($path . $directory) &&
                    $directoryRead->isExist($path . $directory . '/' . 'composer.json')
                ) {
                    $composerJsonData = $directoryRead->readFile($directory . '/' . 'composer.json');
                    $data             = json_decode($composerJsonData, true);
                    if (isset($data['type']) && isset($data['name']) && $data['type'] == 'metapackage') {
                        if (!isset($result[$data['name']])) {
                            $result[$data['name']] = $data;
                        }
                    }
                }
            }
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            return [];
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function readVendorPath()
    {
        $result = [];
        $data   = $this->composerInformation->getInstalledMagentoPackages();

        foreach ($data as $package) {
            if (strpos($package['name'], strtolower(self::VENDOR)) === 0 && $package['type'] == 'metapackage') {
                $result[$package['name']] = $package;
            }
        }

        return $result;
    }
}