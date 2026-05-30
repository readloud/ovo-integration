const OVOID = require('ovoid');

// Configuration - Replace with your actual credentials
const PHONE_NUMBER = '081234567890'; // Replace with your OVO registered phone number
const OVO_PIN = '123456'; // Replace with your OVO PIN (6 digits)

// Helper function to simulate user input (for CLI testing)
const readline = require('readline');
const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout
});

const question = (query) => new Promise((resolve) => {
  rl.question(query, resolve);
});

async function testOVO() {
  console.log('🚀 Starting OVO Integration Test\n');
  
  try {
    // Step 1: Login with 2FA (Request OTP)
    console.log('📱 Step 1: Requesting OTP...');
    const refId = await OVOID.login2FA(PHONE_NUMBER);
    console.log('✅ OTP Request Successful!');
    console.log('   OTP Ref ID:', refId.otp_refId);
    console.log('   Device ID:', refId.device_id);
    console.log('');
    
    // Step 2: Get OTP from user input
    const otpCode = await question('🔐 Enter OTP sent to your phone (6 digits): ');
    console.log('');
    
    // Step 3: Verify OTP
    console.log('🔍 Step 2: Verifying OTP...');
    const accessToken = await OVOID.login2FAVerify(
      refId.otp_refId,
      otpCode,
      PHONE_NUMBER,
      refId.device_id
    );
    console.log('✅ OTP Verified Successfully!');
    console.log('   OTP Token:', accessToken.otp_token.substring(0, 50) + '...');
    console.log('   Expires at:', new Date(parseInt(accessToken.expires_at) * 1000).toLocaleString());
    console.log('');
    
    // Step 4: Login with Security Code (PIN)
    console.log('🔐 Step 3: Authenticating with PIN...');
    const authToken = await OVOID.loginSecurityCode(
      OVO_PIN,
      accessToken.otp_token,
      PHONE_NUMBER,
      refId.otp_refId,
      refId.device_id
    );
    console.log('✅ Authentication Successful!');
    console.log('   Refresh Token:', authToken.refresh_token);
    console.log('');
    
    // Initialize OVOID instance for API calls
    const ovo = new OVOID(authToken.refresh_token);
    console.log('✅ OVOID Instance Created Successfully!\n');
    
    // Test 1: Get Profile
    console.log('👤 Test 1: Getting Profile Info...');
    const profile = await ovo.getProfile();
    console.log('✅ Profile Retrieved:');
    console.log('   Name:', profile.name || 'N/A');
    console.log('   Phone:', profile.phone || PHONE_NUMBER);
    console.log('   Email:', profile.email || 'N/A');
    console.log('');
    
    // Test 2: Get Balance
    console.log('💰 Test 2: Getting Balance...');
    try {
      const cashBalance = await ovo.getBalance('cash');
      console.log('✅ OVO Cash Balance: Rp', cashBalance?.balance || '0');
    } catch (err) {
      console.log('⚠️ Could not fetch cash balance:', err.message);
    }
    
    try {
      const pointBalance = await ovo.getBalance('point');
      console.log('✅ OVO Point Balance:', pointBalance?.balance || '0 points');
    } catch (err) {
      console.log('⚠️ Could not fetch point balance:', err.message);
    }
    console.log('');
    
    // Test 3: Get Unread Notifications
    console.log('🔔 Test 3: Getting Unread Notifications...');
    try {
      const unreadCount = await ovo.getUnreadHistory();
      console.log('✅ Unread Notifications:', unreadCount);
    } catch (err) {
      console.log('⚠️ Could not fetch notifications:', err.message);
    }
    console.log('');
    
    // Test 4: Get All Notifications
    console.log('📬 Test 4: Getting All Notifications...');
    try {
      const notifications = await ovo.getAllNotification();
      console.log('✅ Total Notifications:', notifications?.length || 0);
      if (notifications && notifications.length > 0) {
        console.log('   Latest:', notifications[0].title || 'No title');
      }
    } catch (err) {
      console.log('⚠️ Could not fetch all notifications:', err.message);
    }
    console.log('');
    
    // Test 5: Check if number is OVO registered
    console.log('📞 Test 5: Checking OVO Number...');
    const testNumber = await question('Enter phone number to check if registered on OVO (optional, press Enter to skip): ');
    
    if (testNumber) {
      try {
        const isOVO = await ovo.isOVO(10000, testNumber);
        console.log(`✅ Number ${testNumber} is ${isOVO ? 'registered' : 'not registered'} on OVO`);
      } catch (err) {
        console.log('⚠️ Could not check number:', err.message);
      }
    }
    console.log('');
    
    // Optional: Logout
    const shouldLogout = await question('Do you want to logout? (y/n): ');
    if (shouldLogout.toLowerCase() === 'y') {
      await ovo.logout();
      console.log('✅ Logged out successfully!');
    }
    
    console.log('\n✨ All tests completed successfully!');
    
  } catch (error) {
    console.error('\n❌ Error occurred:');
    console.error('   Message:', error.message);
    console.error('   Details:', error.response?.data || error);
  } finally {
    rl.close();
  }
}

// Run the test
console.log('═══════════════════════════════════════');
console.log('     OVO INTEGRATION TEST SUITE');
console.log('═══════════════════════════════════════\n');

testOVO();