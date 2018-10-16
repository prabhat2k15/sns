# README #

# 1.  Process Company API
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
    - @param array `$keys` -the keys whose details to be fetched
    - @response json 

- Success Response
    - code : 200 
    - ``` {status : true, message:'success message'} ```
- Failure Response
    - code : 200  
    - ``` {status : false, message:'failure message'} ```



### Who do I talk to? ###

* Repo owner or admin
* prabhat.kumar [prabhat.kumar@myoperator.co]
* vtadeshpandey [adesh.pandey@myoperator.co]



