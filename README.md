# ICC Message Feed

This project is intended to assist the Kyneton CFA with creating a monitoring log of alert messages. The messages are consumed from a hosted file, `monitor.buf`. The format of this file has no particular delimination, but with a somewhat regular tokenization based on regular expressions.

There are two message types, `@@ALERT` which indicates an incident with agency response, and units paged. There are also `HbRE` which indicates an update to a previous incident.

The project is a ReactJS rebuild of a previous implementation written in PHP. The React solution is much more concise and terse, with a proper seperation of the message parsing logic and the presentation layer. A later version could move the parser logic into a server to emit alert messages as events to be consumed, eg via websockets.


This project was bootstrapped with [Create React App](https://github.com/facebook/create-react-app).

## Available Scripts

In the project directory, you can run:

### `npm start`

Runs the app in the development mode.<br>
Open [http://localhost:3000](http://localhost:3000) to view it in the browser.

The page will reload if you make edits.<br>
You will also see any lint errors in the console.

### `npm test`

Launches the test runner in the interactive watch mode.<br>
See the section about [running tests](https://facebook.github.io/create-react-app/docs/running-tests) for more information.

### `npm run build`

Builds the app for production to the `build` folder.<br>
It correctly bundles React in production mode and optimizes the build for the best performance.

The build is minified and the filenames include the hashes.<br>
Your app is ready to be deployed!