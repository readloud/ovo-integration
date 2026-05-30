const OVOID = require('ovoid');

// WARNING: Keep credentials secure! Use environment variables in production
const PHONE_NUMBER = process.env.OVO_PHONE || '081234567890';
const OVO_PIN = process.env.OVO_PIN || '123456';
const OTP_CODE = process.env.OVO_OTP || '123456'; // For testing only

async function quickTest() {
  console.log('🚀 Quick OVO Test\n');
  
  try {
    // Login flow
    console.log('1️⃣ Requesting OTP...');
    const refId = await OVOID.login2FA(PHONE_NUMBER);
    console.log(`   ✅ Ref ID: ${refId.otp_refId}`);
    
    console.log('2️⃣ Verifying OTP...');
    const accessToken = await OVOID.login2FAVerify(
      refId.otp_refId,
      OTP_CODE,
      PHONE_NUMBER,
      refId.device_id
    );
    console.log('   ✅ OTP Verified');
    
    console.log('3️⃣ Authenticating with PIN...');
    const authToken = await OVOID.loginSecurityCode(
      OVO_PIN,
      accessToken.otp_token,
      PHONE_NUMBER,
      refId.otp_refId,
      refId.device_id
    );
    console.log('   ✅ Authenticated\n');
    
    const ovo = new OVOID(authToken.refresh_token);
    
    // Get profile
    const profile = await ovo.getProfile();
    console.log('📱 Profile:', JSON.stringify(profile, null, 2));
    
    // Get balance
    const balance = await ovo.getBalance('cash');
    console.log('\n💰 Balance: Rp', balance?.balance);
    
    console.log('\n✅ Test completed!');
    
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
}

quickTest();