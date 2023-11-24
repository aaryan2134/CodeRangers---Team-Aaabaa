const express = require('express');
const bodyParser = require('body-parser');

const app = express();
const port = 3000;

app.use(bodyParser.json());

app.post('/track', (req, res) => {
  const trackingData = req.body;
  console.log('Received tracking data:', trackingData);

  res.status(200).send('Tracking data received successfully');
});

app.listen(port, () => {
  console.log(`Server is running on port ${port}`);
});
