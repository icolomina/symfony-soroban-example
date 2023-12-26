## Introduction
This repository contains a symfony project which can serve as a base to develop soroban dapps using such framework. The project relies on the [soneso php stellar sdk](https://github.com/Soneso/stellar-php-sdk) to deploy, install 
and interact with the contract. 

## Setup
The project setup is pretty easy. Simply follow the below steps:

```shell
git clone git@github.com:icolomina/symfony-soroban-example.git
cd symfony-soroban-example
docker build --no-cache -t crypto-bills .
docker run -it -p 97:80 crypto-bills
```

Building the image can take a few minutes so it has to install dependencies, deploy the contract and the token etc.

### Login to the application

Open a browser and go to the login page: http://localhost:97/login and press the button. It will prompt your freighter wallet to log in. It will take a few seconds since the app mints the logged user 
with tokens.
After logging in, you will see a list of users (only one user). Copy the user address and press the "Create contract" link. It will open a page with a simple form:

- Receiver: Paste here the address you've been copied
- Label: A contract label
- Description: A contract description
- Token: The token to use. Copy the code in parentheses

Then, press the "Create contract" button. It will take a few seconds since it has to install and initialize the contract. After that, you will be redirected to a contract list page and you can see your
recently created contract. 

Now it's time to deposit some tokens. Press the "Send deposit" link. You will see a form field called amount. Write the amount you want to deposit (50 for example) and press the "Send deposit" button. After a few seconds, you will be redirected to the contract list and you will see that your contract balance has been increased (you will see 50). If you make another deposit, you will see 100 in the balance (the contract returns the current contract address balance).

> Do not create more than one contract since the app always make the deposits over the same contract.