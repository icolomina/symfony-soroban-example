#![cfg(test)]

use crate::Bill;
use crate::{CryptoBills, CryptoBillsClient};
use soroban_sdk::{Env, testutils::Address as _, Address, token, symbol_short, vec};
use soroban_sdk::IntoVal;
use soroban_sdk::testutils::Events;
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
    comp: Address,
    client:  CryptoBillsClient<'a>,
    contract_id: Address,
    token: TokenClient<'a>,
    token_admin: TokenAdminClient<'a>
}

fn init_test_data(e: &Env) -> TestData {
    e.mock_all_auths();

    let contract_id = e.register_contract(None, CryptoBills);
    let client = CryptoBillsClient::new(&e, &contract_id);

    let admin = Address::random(&e);
    let user = Address::random(&e);
    let comp = Address::random(&e);
    let (token, token_admin) = create_token_contract(&e, &admin);

    TestData {
        admin,
        user,
        comp,
        client,
        contract_id,
        token,
        token_admin
    }
}

#[test]
fn test_init() {
    let e = Env::default();
    let test_data = init_test_data(&e);
    assert_eq!(test_data.client.init(&test_data.admin, &test_data.user, &test_data.comp, &test_data.token.address), true);
}

#[test]
fn test_deposit() {
    let e = Env::default();
    let test_data = init_test_data(&e);
    test_data.token_admin.mint(&test_data.user, &100);

    test_data.client.init(&test_data.admin, &test_data.user, &test_data.comp, &test_data.token.address);
    assert_eq!(test_data.client.deposit(&50), 50);
}

#[test]
#[should_panic(expected = "HostError: Error(Contract, #2)")]
fn test_fail_not_initialized() {
    let e = Env::default();
    e.mock_all_auths();

    let contract_id = e.register_contract(None, CryptoBills);
    let crypto_bills_client = CryptoBillsClient::new(&e, &contract_id);
    assert_eq!(crypto_bills_client.deposit(&50), 50);
}

#[test]
fn test_emit_bill() {
    let e = Env::default();
    let test_data = init_test_data(&e);

    test_data.client.init(&test_data.admin, &test_data.user, &test_data.comp, &test_data.token.address);
    let bill = Bill {
        cod: symbol_short!("866544679"),
        amount: 150
    };
    assert_eq!(test_data.client.emit_bill(&bill), true);
    let last_events = vec![&e, e.events().all().pop_back().unwrap()];
    assert_eq!(
        last_events,
        vec![
            &e,
            (
                test_data.contract_id.clone(),
                (symbol_short!("t_bill"), ).into_val(&e),
                bill.into_val(&e)

            )
        ]
    );
    

}

#[test]
fn test_pay_bill() {
    let e = Env::default();
    let test_data = init_test_data(&e);

    test_data.token_admin.mint(&test_data.user, &100);

    test_data.client.init(&test_data.admin, &test_data.user, &test_data.comp, &test_data.token.address);
    test_data.client.deposit(&50);
    let bill = Bill {
        cod: symbol_short!("866544679"),
        amount: 30
    };

    assert_eq!(test_data.client.pay_bill(&bill), 30);
}

#[test]
#[should_panic(expected = "HostError: Error(Contract, #1)")]
fn test_pay_bill_inssuficient_balance() {
    let e = Env::default();
    let test_data = init_test_data(&e);

    test_data.token_admin.mint(&test_data.user, &100);

    test_data.client.init(&test_data.admin, &test_data.user, &test_data.comp, &test_data.token.address);
    test_data.client.deposit(&50);
    let bill = Bill {
        cod: symbol_short!("866544679"),
        amount: 60
    };

    test_data.client.pay_bill(&bill);
}
