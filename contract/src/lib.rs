#![no_std]

use soroban_sdk::{contract, contractimpl, contracttype, contracterror, Address, Env, Symbol, token, symbol_short};

pub const BALANCES: Symbol = symbol_short!("b");
pub const BILL_TOPIC: Symbol = symbol_short!("t_bill");
pub const ADMIN: Symbol = symbol_short!("admin");
pub const TOKEN: Symbol = symbol_short!("token");
pub const USER: Symbol = symbol_short!("user");
pub const COMP: Symbol = symbol_short!("comp");
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

#[contracttype]
#[derive(Clone)]
pub struct Bill {
    cod: Symbol,
    amount: i128,
}

fn get_state(env: &Env) -> State {
    if let Some(s) = env.storage().persistent().get(&STATE) {
        return s;
    }
    return State::Pending;
}

#[contract]
pub struct CryptoBills;

#[contractimpl]
impl CryptoBills {

    pub fn init(env: Env, admin_addr: Address, user_addr: Address, comp_addr: Address, token_addr: Address) -> Result<bool, Error>{
        env.storage().persistent().set(&ADMIN, &admin_addr);
        env.storage().persistent().set(&TOKEN, &token_addr);
        env.storage().persistent().set(&USER, &user_addr);
        env.storage().persistent().set(&COMP, &comp_addr);
        env.storage().persistent().set(&STATE, &State::Initialized);
        Ok(true)
    }
    
    
    pub fn deposit(env: Env, amount: i128) -> Result<i128, Error> {

        if get_state(&env) != State::Initialized {
            return Err(Error::ContractNotInitialized);
        }

        
        let token: Address = env.storage().persistent().get(&TOKEN).unwrap();
        let user: Address = env.storage().persistent().get(&USER).unwrap();
        user.require_auth();
        
        let tk = token::Client::new(&env, &token);
        tk.transfer(&user, &env.current_contract_address(), &amount);
        let current_contract_balance = tk.balance(&env.current_contract_address());
        Ok(current_contract_balance)
    }

    pub fn pay_bill(env: Env, bill: Bill) -> Result<i128, Error> {

        if get_state(&env) != State::Initialized {
            return Err(Error::ContractNotInitialized);
        }

        let admin_addr: Address = env.storage().persistent().get(&TOKEN).unwrap();
        admin_addr.require_auth();

        let token: Address = env.storage().persistent().get(&TOKEN).unwrap();
        let comp: Address = env.storage().persistent().get(&COMP).unwrap();

        let tk = token::Client::new(&env, &token);
        if tk.balance(&env.current_contract_address()) < bill.amount {
            return Err(Error::InsufficientBalance);
        }

        tk.transfer(&env.current_contract_address(), &comp, &bill.amount);
        Ok(bill.amount)

    }

    pub fn emit_bill(env: Env, bill: Bill) -> Result<bool, Error> {

        if get_state(&env) != State::Initialized {
            return Err(Error::ContractNotInitialized);
        }

        let comp_addr: Address = env.storage().persistent().get(&COMP).unwrap();
        comp_addr.require_auth();

        env.events().publish((BILL_TOPIC, ), bill);
        Ok(true)
    }
}

mod test;

