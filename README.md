## Introduction
This repository contains a symfony project which can serve as a base to develop soroban dapps using such framework. The project relies on the [soneso php stellar sdk](https://github.com/Soneso/stellar-php-sdk) to deploy, install 
and interact with the contract. 

## The contract
The contract code is located under the *contract* folder. It is written in rust and contains two methods:
- **init**: Initialize the contract.
- **deposit**: Allows an address to transfer funds to the contract address.

### Test and compile the contract
Navigate to the contract folder and execute the following command:

```shell
cargo test
```
> If you've not installed cargo yet, refer to the [soroban setup docs](https://soroban.stellar.org/docs/getting-started/setup).

The last command will compile the contract and then execute the tests. After passing all tests, you are ready to generate the wasm file. Let's generate it:

```shell
soroban contract build
```
This command creates the contract wasm file on the folder *target/wasm32-unknown-unknown/release*. You don't have to move the wasm file to any folder. The project will search and get wasm file contents for you.

### Create a Key pair 
We need to create a key pair so the project can deploy the contract. Go to the [stellar laboratory](https://laboratory.stellar.org/) and follow the next steps: 
- Select the *test* network and the *Create Account* tab.
- Click on the *Generate keypair* button. You will see a new public and private key.
- Copy the public key and paste it in the input below. Then click on *Get test network lumens* to fund the account.
- Save the public and private keys since we will need them later.

### Test and compile the token
The project needs a token so the contract can transfer the funds. We can use the [soroban-token-example](https://github.com/stellar/soroban-examples/tree/v20.0.0-rc2/token). 
First of all, create a new folder called *token* in the root project:

```shell
mkdir token
```
Now, download the soroban examples and move the token folder contents to your *token* folder:

```shell
git clone -b v20.0.0-rc2 https://github.com/stellar/soroban-examples
cd soroban-examples
mv token/* <your_project_dir>/token/
```
Once moved, go to your *token* folder and compile and test as we did in the last section:
```shell
cargo test
```
After ensuring that the tests pass, generate the token contract wasm file:
```shell
soroban contract build
```
To finish, we have to deploy and initialize the token
- Add the testnet network if you have not added it yet

```shell
soroban config network add --global testnet --rpc-url https://soroban-testnet.stellar.org:443 --network-passphrase "Test SDF Network ; September 2015"
```

- Deploy the token
```shell
bin/console contract:deploy --type=token --install
```
The deploy command will return the contract identifier. Save it since we'll also need it later.

- Initialize the token

```shell
soroban contract invoke --id <contract_token_id> --source <your_private_key> --network testnet -- initialize --admin <your_public_key> --decimal 4 --name "My Token contract" --symbol "MTI"
```

Now we have the token ready. In the later sections, we will see how to mint the user accounts with our token.


## Preparing the environment
Now we have the contract wasm and the token deployed and initialized, we can start to prepare the application environment.

### Install dependencies
This project uses two kinds of dependencies, php dependencies which are managed with composer and javascript dependencies which are managed with npm. To install both of them, execute the following commands in your project root folder:
```shell
composer install
npm install
```

### The environment vars
This project holds a *.env.dist* with the environment variables required. Create a *.env* file (or rename the *.env.dist* file) and set the corresponding values to the variables:

- **SOROBAN_SECRET_KEY**: The secret key we generated on the stellar laboratory
- **SOROBAN_PUBLIC_KEY**: The public key we generated on the stellar laboratory
- **SOROBAN_TOKEN_ADDR**: The token contract address we generated in the last section
- **APP_SECRET**: This var is not important. You can set a random string
- **DATABASE_URL**: Holds the database connection uri. Change the file name to one of your choice
- **SOROBAN_CONTRACT_WASM_ID**: Holds the contract wasm id which will be used to generate a contract id.

To generate the wasm id, deploy the contract so you can get it. To do it, execute the following command in your project root folder:
```shell
bin/console contract:deploy
```
It will print the wasm id. Copy and set it as the *SOROBAN_CONTRACT_WASM_ID* value.

### Initialize the database
Before creating the database install [sqlite](https://www.sqlite.org/index.html).
```shell
sudo apt install sqlite3
sudo apt install php8.1-sqlite3 
```
The project database is managed with [doctrine](https://symfony.com/doc/current/doctrine.html). Execute the following command to create the database
```shell
bin/console doctrine:schema:create
```
Then, load the database with data using the [doctrine fixtures](https://symfony.com/bundles/DoctrineFixturesBundle/current/index.html) load command:
```shell
bin/console doctrine:fixtures:load
```
The fixtures command generates a new key pair for each user and funds it with the test friendbot. The secret seed is stored in the *secret* field and the public key is stored in the *address* field.
> In a real custodial environment, you should store your secret keys with strongest security measures.

Before continuing, we have to mint the user1 and user2 addresses. To do it, follow the next steps:

- Open an SQLite shell and query the user table
```shell
sqlite3 var/<yous_name>.db
Select * from users
```

- Mint the user addresses using the following command:
```shell
soroban contract invoke --id <token_contract_id> --source <soroban_private_key> --network testnet -- mint  --to <user_public_key>  --amount 500000000000
```

## The application

Now, it's time to open the application. Open two terminals and execute the following commands:

- In the first terminal
```shell
npm run dev
```
This command compiles the assets and publishes them to the public directory using [symfony webpack](https://symfony.com/doc/current/frontend/encore/index.html)

```shell
symfony server:start
```
This command starts a development server. 

### Login to the application

Open a browser and go to the login page: http://127.0.0.1:8000/login. You should see the following login page:

![Login Page](/docs/images/login_app.png)

> Open the *src/DataFixtures/UserFixtures.php* file to get the user credentials.

### Users list

After login, you will see the following page:

![Users list page](/docs/images/users_list_app.png)

Click on the *Create contract* link to go to create the contract

### Create contract 

After clicking on *Create contract*, you will see a page with a form:

![Create contract Page](/docs/images/create_contract_app.png)

Fill the form with the user listed on the previous page (*user2@domain.com*), a label and a description of your choice and the token code you deployed and installed previously. The click on *Create Contract* button. It will take some time and then you will see the contract list page:

![Contract list Page](/docs/images/contract_list_app.png)

### Sending a deposit

To send a deposit, click on the *Send deposit* link. You will see the following page:

![Send deposit Page](/docs/images/send_deposit.png)

Introduce an amount and click on the *Send deposit* button.

> I hve problems sending the deposit because the contract token transfer funcion throws a function trapped error. 

### Plus
There is a *stimulus* controller on the path *assets/controllers/wallet_controller.js* which shows how to interact with the freighter wallet and get the user's public key.