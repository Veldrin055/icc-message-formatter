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
    const intervalId = setInterval(this.update, 3000);
    this.setState({ intervalId });  
  }
  
  componentWillUnmount() {
    clearInterval(this.state.intervalId);
  }
  
  update = () => {
    fetch('monitor.buf')
      .then(response => {
        return response.text();
      })
      .then(body => {
        this.setState({ events: parser(body) });
      });
  }

  updatePaging = (event) => {
    this.setState({ value: event.target.value });
  }

  render() {
    const { value } = this.state;
    const events = this.state.events.slice(0, value);
    return (
      <div className="App" >
        <MainPager {...{ events }}/>
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
