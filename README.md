

### Manual Installation

 - Unzip the zip file in `app/code`
 - Apply compile and  upgrade  by running `php bin/magento setup:di:compile && php bin/magento setup:upgrade && php bin/magento cache:flush`

### Commands
 - Console Command
	- JSON profile - Place json inside var/import/ folder 
	-`php bin/magento customer:import sample-json var/import/sample.json`
    - CSV profile - Place CSV inside var/import/ folder
    -`php bin/magento customer:import sample-csv var/import/sample.csv`
	
## Supported
Tested on Magento CLI 2.4.3-p1 with php 7.4	



