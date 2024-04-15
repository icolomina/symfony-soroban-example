## Introduction
This repository contains a symfony project which can serve as a base to develop soroban dapps using such framework. The project relies on the [soneso php stellar sdk](https://github.com/Soneso/stellar-php-sdk) to deploy, install 
and interact with the contract. 

## Setup
The project setup is pretty easy. Simply follow the below steps:

```shell
git clone https://github.com/icolomina/symfony-soroban-example.git 
cd symfony-soroban-example
docker build --no-cache -t crypto-bills .
docker run -it -p 97:80 crypto-bills
```

Building the image can take a few minutes so it has to install dependencies, deploy the contract and the token etc.

### Login to the application

Follow this article to navigate througth te application:
- https://dev.to/icolomina/making-deposits-to-an-smart-contract-using-php-symfony-and-the-soroban-technology-4f10
