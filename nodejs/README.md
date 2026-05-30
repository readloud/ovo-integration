## <center>Un-Official OVO.id API Wrapper for NodeJS</center>

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

```bash
npm install
npm build
```

Run interactive test (recommended):

`node test-ovo.js`

Run automated test (with hardcoded OTP):

`node test-ovo-auto.js`

### Set environment variables (optional)

```env
export OVO_PHONE="081234567890"
export OVO_PIN="123456"
export OVO_OTP="123456"
```

### Troubleshooting
 
`node test-diagnostic.js`

Important Notes:
Replace credentials: Change PHONE_NUMBER and OVO_PIN with your actual OVO account credentials
OTP delivery: The OTP code will be sent to your phone number via SMS
Rate limits: Don't request OTP too frequently
Ready to test? Replace the phone number and PIN in test-ovo.js
