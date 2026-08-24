const fs = require('fs');
let content = fs.readFileSync('C:/laragon/www/Masar/backend/MASAR.json', 'utf8');
const obj = JSON.parse(content);
// Find Create Application request
const createApp = obj.item[4].item[4].item[0];
console.log('Method:', createApp.request.method);
console.log('Body mode:', createApp.request.body.mode);
console.log('Body formdata entries:');
for (let i = 0; i < createApp.request.body.formdata.length; i++) {
  const f = createApp.request.body.formdata[i];
  console.log('  key: ' + f.key + ', type: ' + f.type + ', value: ' + f.value);
}
console.log('Has cv_file_id key:', createApp.request.body.formdata?.some(f => f.key === 'cv_file_id') ? 'YES' : 'NO');
console.log('Has cv key:', createApp.request.body.formdata?.some(f => f.key === 'cv') ? 'YES' : 'NO');
console.log('cv type:', createApp.request.body.formdata?.find(f => f.key === 'cv')?.type);