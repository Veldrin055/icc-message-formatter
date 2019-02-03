import React, { Component } from 'react';
import './App.css';
import './new_style.css';
import parser from './parser/parser';
import MainPager from './MainPager';

class App extends Component {
  state = {
    events: [],
  };

  componentDidMount() {
    fetch('monitor.buf')
      .then(response => {
        return response.text();
      })
      .then(body => {
        this.setState({ events: parser(body) });
      });
  }

  render() {
    const { events } = this.state;
    return (
      <div className="App" >
        <MainPager {...{ events }}/>
      </div>
    );
  }
}

export default App;
