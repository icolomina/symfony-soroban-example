## Introduction
This repository contains a symfony project which can serve as a base to develop soroban dapps using such framework. The project relies on the [soneso php stellar sdk](https://github.com/Soneso/stellar-php-sdk) to deploy, install 
and interact with the contract. 

## The contract
The contract code is located under the *contract* folder. It is written in rust and contains two methods:
- **init**: Initialize the contract.
- **deposit**: Allows an address to transfer funds from is address to the contract address.

### Test and compile the contract
Navidate to the contract folder and execute the following command:

```shell
cargo test
```
> If you've not installed cargo yet, refer to the [soroban setup docs](https://soroban.stellar.org/docs/getting-started/setup).

The last command will compile the contract and then execute the tests. After passing all tests, you are ready to generate the wasm file. Let's generate it:

```shell
soroban contract build
```
This command creates the contract wasm file on the folder *target/wasm32-unknown-unknown/release*. You don't have to move the wasm file to any folder. The project will search and get wasm
file contents for you.

### Create a Key pair 
We need to create a key pair so the project can deploy the contract. Go to the [stellar laboratory](https://laboratory.stellar.org/) and follow the next steps: 
- Select the *test* network and the *Create Account* tab.
- Click on the *Generate keypair* button. You will se a new public and private key.
- Copy the public key and paste it in the input bellow. Then click on *Get test network lumens* to fund the account.
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
To finish, we have to deploy the token contract to the testnet since we will need its address later to initialize our contract. Go to your token folder and deploy it executing the following command:

```shell
soroban config network add --global testnet \
  --rpc-url https://soroban-testnet.stellar.org:443 \
  --network-passphrase "Test SDF Network ; September 2015"

soroban contract deploy \
  --wasm target/wasm32-unknown-unknown/release/soroban-token-wasm.wasm \
  --source <your_private_key> \
  --network testnet
```
> The first command is only necessary if you have not added the testnet network so far.

The deploy contract will return the contract identifier. Save it since we'll also need it later.

Now, we have to initialize the token contract:

## Preparing the environment
Now we have the contract wams and the token deployed and initialized, we can start to prepare the application enviroment.

### Install dependencies
This project uses two kind of dependencies, php dependencies which are managed with composer and javascript dependencies which are manager with npm. To install both of them, execute the following commands in your project root folder:
```shell
composer install
npm install
```

### The enviroment vars
This project holds a *.env.dist* with the environment variables required. Create a *.env* file (or rename the *.env.dist* file) and set the corresponding values to the variables:

- **SOROBAN_SECRET_KEY**: The secret key we generated on the stellar laboratory
- **SOROBAN_PUBLIC_KEY**: The public key we generated on the stellar laboratory
- **SOROBAN_TOKEN_ADDR**: The token contract address we generated in the last section
- **APP_SECRET**: This var is not important. Yo can set a random string
- **DATABASE_URL**: Holds the database connection uri. Change the file name by one of yout choice

### Deploy the contract
Deploy the contract so you can get the wasm id. To do it, execute the following command in your project root folder:
```shell
bin/console contract:deploy
```
It will print the wasm id.

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
Then, load the database with data using [doctrine fixtures](https://symfony.com/bundles/DoctrineFixturesBundle/current/index.html) command:
```shell
bin/console doctrine:fixtures:load
```
The fixtures command generates a new keyPair for each user and funds it with the test friendbot. The secret seed is stored in the *secret* field and the public key is stored in the *address* field.
> In a real custodial environment, you should store yous secret keys with a strongest security measures.

Before continuing, we have to mint with tokens to the address of user1 and user2. To do it, let's open first an sqlite shell. Go to your project root folder and execute the following command:
```shell
sqlite3 var/<yous_name>.db
```
Then query the users:
```sql
Select * from users
```
Now, mint the users addresses using the following command:

## The application

Now, it's time to open the application. Open two terminals and execute the following commands:

- In the first terminal
```shell
npm run dev
```
This command copiles the assets and publishes it to the public directory using [symfony webpack](https://symfony.com/doc/current/frontend/encore/index.html)

```shell
symfony server:start
```
This command starts a development server. 




