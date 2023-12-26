#![cfg(test)]

use crate::{CryptoDeposit, CryptoDepositClient};
use soroban_sdk::{Env, testutils::Address as _, Address, token};
use token::Client as TokenClient;
use token::StellarAssetClient as TokenAdminClient;

fn create_token_contract<'a>(e: &Env, admin: &Address) -> (TokenClient<'a>, TokenAdminClient<'a>) {
    let contract_address = e.register_stellar_asset_contract(admin.clone());
    (
        TokenClient::new(e, &contract_address),
        TokenAdminClient::new(e, &contract_address),
    )
}

struct TestData<'a> {
    admin: Address,
    user: Address,
    client:  CryptoDepositClient<'a>,
    token: TokenClient<'a>,
    token_admin: TokenAdminClient<'a>
}

fn init_test_data(e: &Env) -> TestData {
    e.mock_all_auths();

    let contract_id = e.register_contract(None, CryptoDeposit);
    let client = CryptoDepositClient::new(&e, &contract_id);

    let admin = Address::generate(&e);
    let user = Address::generate(&e);
    let (token, token_admin) = create_token_contract(&e, &admin);

    TestData {
        admin,
        user,
        client,
        token,
        token_admin
    }
}

#[test]
fn test_init() {
    let e = Env::default();
    let test_data = init_test_data(&e);
    assert_eq!(test_data.client.init(&test_data.admin, &test_data.token.address), true);
}

#[test]
fn test_deposit() {
    let e = Env::default();
    let test_data = init_test_data(&e);
    test_data.token_admin.mint(&test_data.user, &100);

    test_data.client.init(&test_data.admin, &test_data.token.address);
    assert_eq!(test_data.client.deposit(&test_data.user, &50), 50);
}

#[test]
#[should_panic(expected = "HostError: Error(Contract, #2)")]
fn test_fail_not_initialized() {
    let e = Env::default();
    e.mock_all_auths();
    let test_data = init_test_data(&e);

    let contract_id = e.register_contract(None, CryptoDeposit);
    let crypto_bills_client = CryptoDepositClient::new(&e, &contract_id);
    assert_eq!(crypto_bills_client.deposit(&test_data.user, &50), 50);
}
