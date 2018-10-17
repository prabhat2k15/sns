#   Process Company API
This api fetches different details of the company and push it to SNS.

### How do I  set up? ###
* git clone repo
* cd repo-name
* composer install(for production, composer install --no-dev)
* cp .env.example .env (copy enviroment file)
* Set the proper credentials

#### Directory Structure
The structure follows psr-4 standards
```
├── README.md
├── app
│   ├── controller
│   │   └── ProcessCompanyController.php
│   ├── model
│   │   ├── Database.php
│   │   └── Query.php
│   └── service
│       ├── ProcessCompany.php
│       ├── SNS.php
│       └── Validation.php
├── companydetails.json
├── composer.json
├── composer.lock
├── config.copy.php
├── config.php
├── index.php
├── logconf.php
├── phpunit.xml
└── tests
    └── ProcessCompanyTest.php
```

### API
- URL `/`
- Method `GET` or `POST` any 
    - @param string `$display_number` -company's display number
    - @param array `$keys` (optional)-the keys whose details to be fetched. If not given, all details of the company will be fetched
    - @response json 

- Success Response
    - code : `200` 
    - ``` {status : true, message:'success message'} ```
- Failure Response
    - code : `401` or `404`  
    - ``` {status : false, message:'failure message'} ```
- Example 
    - /?display_number=919873832455 
        - Fetches all data of the company whose display number is given 
    - /?display_number=919873832455&keys={"companies":{"destination":[],"destination_2":[]}}
        - Fetches compnay details whose display no is given with destination and destination_2 with their respective values fetched from db
    - /?display_number=919873832455&keys={"companies":{}, "ivrs":{}}
        - Fetches details of the compnay and ivrs whose display number is given

### Tests
To run test,
- type `vendor/bin/phpunit` and hit enter

### Documents
You can generate the docs by using following steps.
-   cd repo-dir
-   type `vendor/bin/phpdoc` in the terminal and hit enter
The documents will be created in docs folder.

You will find the detailed documentation at the given below url
- /docs
### Who do I talk to? ###

* Repo owner or admin
* prabhat.kumar [prabhat.kumar@myoperator.co]
* vtadeshpandey [adesh.pandey@myoperator.co]



