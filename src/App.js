import React, { Component } from 'react';
import './assets/App.css';
import './assets/new_style.css';
import soundfile from './assets/click.mp4';
import warning from './assets/WarningTone.wav';
import parser from './parser/parser';
import MainPager from './MainPager';
import Paging from './Paging';
import { isSpecialAlert } from './utils';

class App extends Component {
  constructor(props) {
    super(props);
    this.state = {
      events: [],
      value: 100,
      updating: false,
      error: false,
      isNew: false,
    };
    this.audio = new Audio(soundfile);
    this.warning = new Audio(warning);
  }

  componentDidMount() {
    this.update();
    this.intervalId = setInterval(this.update, 3000);
  }

  componentWillUnmount() {
    clearInterval(this.intervalId);
  }

  update = () => {
    const { events } = this.state;
    this.setState({ error: false, updating: true });
    // fetch('monitor.buf')
    fetch('http://fs.kynvic.net/pubicc/monitor.buf')
      .then(response => {
        if (!response.ok) {
          throw new Error({
            status: response.status,
            msg: response.statusText,
          });
        }
        return response.text();
      })
      .then(body => {
        const update = parser(body);
        const isNew = update && update.length && events && events.length
         && update[0].eventId !== events[0].eventId;
        if (isNew) {
          if (isSpecialAlert(update[0].brigades)) {
            this.warning.play();
          } else {
            this.audio.play();
          }
        }
        this.setState({ events: update, updating: false, error: false });
      })
      .catch(err => {
        console.error(err);
        this.setState({ error: true, updating: false });
      });
  };

  updatePaging = event => {
    this.setState({ value: event.target.value });
  };

  render() {
    const { value, error, updating } = this.state;
    const events = this.state.events.slice(0, value);
    return (
      <div className="App">
        <MainPager {...{ events, error, updating }} />
        <Paging {...{ onChange: this.updatePaging, value, values: [25, 50 ,75, 100, 150, 200, 250, 300]}} />
      </div>
    );
  }
}

export default App;
