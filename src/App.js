import React, { Component } from 'react';
import './App.css';
import './new_style.css';
import parser from './parser/parser';
import MainPager from './MainPager';

class App extends Component {
  
  state = {
    events: [],
    value: 100,
  };

  componentDidMount() {
    this.update();
    this.intervalId = setInterval(this.update, 3000);
  }

  componentWillUnmount() {
    clearInterval(this.intervalId);
  }

  update = () => {
    this.setState({ error: false, updating: true });
    fetch('monitor.buf')
    // fetch('http://fs.kynvic.net/pubicc/monitor.buf')
      .then(response => {
        if (!response.ok) {
          throw new Error({ status: response.status, msg: response.statusText });
        }
        return response.text();
      })
      .then(body => {
        this.setState({ events: parser(body), updating: false, error: false });
      })
      .catch(err => {
        console.error(err)
        this.setState({ error: true, updating: false })
      });
  }

  updatePaging = (event) => {
    this.setState({ value: event.target.value });
  }

  render() {
    const { value, error, updating } = this.state;
    const events = this.state.events.slice(0, value);
    return (
      <div className="App" >
        <MainPager {...{ events, error, updating }}/>
        <Paging {...{onChange: this.updatePaging, value}}/>
      </div>
    );
  }
}

const Paging = ({onChange, value}) => {
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
}

export default App;
