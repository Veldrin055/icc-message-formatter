import React, { Component } from 'react';
import './App.css';
import './new_style.css';
import parser from './parser/parser';
import MainPager from './MainPager';
import soundfile from './click.mp4';

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
        const isNew =
          update && update.length && events && events.length && update[0].eventId !== events[0].eventId;
        if (isNew) {
          this.audio.play();
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
        <Paging {...{ onChange: this.updatePaging, value }} />
      </div>
    );
  }
}

const Paging = ({ onChange, value }) => {
  return (
    <div className="paging">
      <b>PAGING: </b>
      <select name="recstoshow" onChange={onChange} defaultValue={value}>
        <option value={25}>25</option>
        <option value={50}>50</option>
        <option value={75}>75</option>
        <option value={100}>100</option>
        <option value={150}>150</option>
        <option value={200}>200</option>
        <option value={250}>250</option>
        <option value={300}>300</option>
      </select>
    </div>
  );
};

export default App;
