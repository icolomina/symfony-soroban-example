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

### The token
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

