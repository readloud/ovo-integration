// Check if OVOID is properly installed
try {
  const OVOID = require('ovoid');
  console.log('✅ OVOID package found');
  console.log('   Version:', require('ovoid/package.json').version);
  console.log('   Methods:', Object.keys(OVOID));
} catch (err) {
  console.error('❌ OVOID not found. Run: npm install ovoid');
}

// Check Node version
console.log('Node version:', process.version);