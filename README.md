# Un-Official OVO API Wrapper

![enter image description here](https://github.com/readloud/ovo-integration/blob/main/nodejs/ovo-unofficial.png)

### Method

- [x] login2FA
- [x] login2FAVerify
- [x] loginSecurityCode
- [x] getBalance
- [x] getBudget
- [x] logout
- [x] unreadHistory
- [x] getWalletTransaction
- [x] generateTrxId
- [x] transferOvo
 

### Instalasi
```
# Install via Composer
composer require svn/ovoid

# Or manually
git clone https://github.com/readloud/ovo-integration.git
cd ovo-integration
composer install

# Run examples
php examples/login.php
php examples/balance.php
php examples/transfer.php
php examples/transaction_history.php
php examples/profile.php
php examples/notifications.php

or

php run.php
```
### versi NodeJS

```bash
# Install

npm install
npm build

# Run interactive test (recommended):

node tests/test-ovo.js

# Run automated test (with hardcoded OTP):

node tests/test-ovo-auto.js
```

### Set environment variables (optional)

```env
export OVO_PHONE="081234567890"
export OVO_PIN="123456"
export OVO_OTP="123456"
```

### Troubleshooting
 
`node tests/test-diagnostic.js`

***Reference***

- [@lintangtimur/ovoid](https://github.com/lintangtimur/ovoid)

- [@anysz/ovopy](https://github.com/anysz/ovopy)

- [@maulana20/ovoid-flutter](https://github.com/maulana20/ovoid-flutter)

- [@adibaulia/ovoid-go](https://github.com/adibaulia/ovoid-go)

- [@namdevel/ovoid-ruby](https://github.com/namdevel/ovoid-ruby)

***Important Notes:***

 - Replace credentials: Change PHONE_NUMBER and OVO_PIN with your actual OVO account credentials
 - OTP delivery: The OTP code will be sent to your phone number via SMS
 - Rate limits: Don't request OTP too frequently
 - Ready to test? Replace the phone number and PIN in `test-ovo.js`
