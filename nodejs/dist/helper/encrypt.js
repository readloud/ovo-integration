"use strict";

const {
  ovoAuth
} = require('./request');
const constants = require("constants");
const crypto = require('crypto');
const {
  APP_VERSION,
  OS_NAME,
  USER_AGENT,
  OS_VERSION,
  CLIENT_ID
} = require('../../config/base');
const encryptRSA = async (securityCode, deviceId, phoneNumber, otpRefId) => {
  var _RSA$data, _RSA$data$keys$;
  const headers = {
    'App-Version': APP_VERSION,
    'User-Agent': USER_AGENT,
    'OS': OS_NAME,
    'OS-Version': OS_VERSION,
    'client-id': CLIENT_ID
  };
  var d = new Date();
  var n = d.getTime();
  let currentTimeMillies = n;
  const RSA = await ovoAuth.get('v3/user/public_keys', null, headers);
  let string = "LOGIN|" + securityCode + "|" + currentTimeMillies + "|" + deviceId + "|" + phoneNumber + "|" + deviceId + "|" + otpRefId;
  return crypto.publicEncrypt({
    "key": (_RSA$data = RSA.data) === null || _RSA$data === void 0 ? void 0 : (_RSA$data$keys$ = _RSA$data.keys[0]) === null || _RSA$data$keys$ === void 0 ? void 0 : _RSA$data$keys$.key,
    padding: constants.RSA_PKCS1_PADDING
  }, Buffer.from(string, "utf8")).toString("base64");
};
module.exports = {
  encryptRSA
};