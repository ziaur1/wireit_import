<?php
declare(strict_types=1);

namespace Wireit\Import\Model\Customer;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Symfony\Component\Console\Input\InputInterface;
use Wireit\Import\Api\Data\ImportInterface;

class CsvImporter implements ImportInterface
{
    protected $keys;
    protected $csv;
    /**
     * @var File
     */
    private $file;
    

    /**
     * CsvImporter constructor.
     * @param File $file
     * @param Csv $csv
     */
    public function __construct(
        File $file,
        Csv $csv
      
    ) {
        $this->csv = $csv;
        $this->file = $file;
     
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
     * @throws LocalizedException
     */
    public function readData(string $file): array
    {
        try {
            if (!$this->file->isExists($file)) {
                throw new LocalizedException(__('Invalid file path or no file found.'));
            }
            $this->csv->setDelimiter(",");
            $data = $this->csv->getData($file);         
        } catch (FileSystemException $e) {           
            throw new LocalizedException(__('File system exception' . $e->getMessage()));
        }

        return $this->formatData($data);
    }

    /**
	 * @Removing headers
     * @param array $data
     * @return array
     */
    public function formatData($data): array
    {   
        $this->keys = array_shift($data);
        array_walk($data, function (&$v) {
            $v = array_combine($this->keys, $v);
        });

        return $data;
    }
}
