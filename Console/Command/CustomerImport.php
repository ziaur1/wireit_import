<?php
declare(strict_types=1);

namespace Wireit\Import\Console\Command;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wireit\Import\Api\Data\ImportInterface;
use Wireit\Import\Model\Customer\CsvImporter;
use Wireit\Import\Model\Customer\JsonImporter;

class CustomerImport extends Command
{
    protected $importer;
    protected $csvFactory;
	protected $JsonFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var CustomerInterfaceFactory
     */
    private $customerInterfaceFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * CustomerImport constructor.
     * @param JsonImporter $csvFactory
	 * @param CsvImporter $csvFactory
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CsvImporter $csvFactory,
		JsonImporter $JsonFactory,
        CustomerInterfaceFactory $customerInterfaceFactory,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct();
        $this->storeManager = $storeManager;
        $this->CsvImporter = $csvFactory;
		$this->JsonImporter = $JsonFactory;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->customerRepository = $customerRepository;
		
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $profileType = $input->getArgument(ImportInterface::PROFILE_NAME);
        $filePath = $input->getArgument(ImportInterface::FILE_PATH);
        $output->writeln(sprintf("Profile type chosen is %s", $profileType));
        $output->writeln(sprintf("File Path is %s", $filePath));      
		   if($profileType=='sample-csv')
		   {
			   $importData = $this->CsvImporter->getImportData($input);
		   }
		   else if($profileType === "sample-json")
		   {
			    $importData = $this->JsonImporter->getImportData($input);
		   }
		 if (isset($importData)) {	
            $this->saveCustomers($importData);
            $output->writeln(sprintf("Total of %s Customers are imported", count($importData)));
            return Cli::RETURN_SUCCESS;
        }else
		{
			$output->writeln("Please check command parameter");
        }			
		
		
        return Cli::RETURN_FAILURE;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName("customer:import");
        $this->setDescription("Customer Import");
        $this->setDefinition([
            new InputArgument(ImportInterface::PROFILE_NAME, InputArgument::REQUIRED, "Profile name ex: sample-csv"),
            new InputArgument(ImportInterface::FILE_PATH, InputArgument::REQUIRED, "File Path ex: sample.csv")
        ]);
        parent::configure();
    }


   

    /**
     * @param $customers
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveCustomers($customers): void
    {
        $storeId = $this->storeManager->getStore()->getId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        foreach ($customers as $data) {
            try {
                $customer = $this->customerInterfaceFactory->create();				
                $customer->setFirstname($data['fname']);
                $customer->setLastname($data['lname']);
                $customer->setEmail($data['emailaddress']);
                $customer->setWebsiteId($websiteId);
				 

                try {
                    $this->customerRepository->save($customer);
                } catch (InputException $e) {
                } catch (InputMismatchException $e) {
                } catch (LocalizedException $e) {
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}
