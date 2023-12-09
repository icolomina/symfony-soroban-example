#![no_std]

use soroban_sdk::{contract, contractimpl, contracttype, contracterror, Address, Env, Symbol, token, symbol_short};

pub const BALANCES: Symbol = symbol_short!("b");
pub const BILL_TOPIC: Symbol = symbol_short!("t_bill");
pub const ADMIN: Symbol = symbol_short!("admin");
pub const TOKEN: Symbol = symbol_short!("token");
pub const STATE: Symbol = symbol_short!("state");

#[derive(Copy, Clone, Debug, Eq, PartialEq, PartialOrd, Ord)]
#[repr(u32)]
#[contracterror]
pub enum Error {
    InsufficientBalance = 1,
    ContractNotInitialized = 2
}

#[derive(Copy, Clone, Debug, Eq, PartialEq, PartialOrd, Ord)]
#[repr(u32)]
#[contracttype]
pub enum State {
    Pending = 1,
    Initialized = 2
}
fn get_state(env: &Env) -> State {
    if let Some(s) = env.storage().persistent().get(&STATE) {
        return s;
    }
    return State::Pending;
}

#[contract]
pub struct CryptoDeposit;

#[contractimpl]
impl CryptoDeposit {

    pub fn init(env: Env, admin_addr: Address, token_addr: Address) -> Result<bool, Error>{
        env.storage().persistent().set(&ADMIN, &admin_addr);
        env.storage().persistent().set(&TOKEN, &token_addr);
        env.storage().persistent().set(&STATE, &State::Initialized);
        Ok(true)
    }
    
    
    pub fn deposit(env: Env, addr: Address, amount: i128) -> Result<i128, Error> {

        if get_state(&env) != State::Initialized {
            return Err(Error::ContractNotInitialized);
        }

        addr.require_auth();
        let token: Address = env.storage().persistent().get(&TOKEN).unwrap();
        
        let tk = token::Client::new(&env, &token);
        tk.transfer(&addr, &env.current_contract_address(), &amount);
        let current_contract_balance = tk.balance(&env.current_contract_address());
        Ok(current_contract_balance)
    }
}

mod test;

