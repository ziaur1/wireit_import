<?php
declare(strict_types=1);

namespace Wireit\Import\Model\Customer;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\SerializerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Wireit\Import\Api\Data\ImportInterface;

class JsonImporter implements ImportInterface
{
    /**
     * @var File
     */
    private $file;
   
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * CsvImporter constructor.
     * @param File $file
     * @param SerializerInterface $serializer    
     */
    public function __construct(
        File $file,
        SerializerInterface $serializer

    ) {
        $this->file = $file;       
        $this->serializer = $serializer;
    }
    /**
     * @inheritDoc
     */
    public function getImportData(InputInterface $input): array
    {
        $file = $input->getArgument(ImportInterface::FILE_PATH);
        return $this->readData($file);
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws \Exception
     */
    public function readData(string $file): array
    {
        try {
            if (!$this->file->isExists($file)) {
                throw new LocalizedException(__('Invalid file path or no file found.'));
            }
            $data = $this->file->fileGetContents($file);          
        } catch (FileSystemException $e) {          
            throw new LocalizedException(__('File system exception' . $e->getMessage()));
        }

        return $this->formatData($data);
    }

    public function formatData($data): array
    {
        return $this->serializer->unserialize($data);
    }
}
