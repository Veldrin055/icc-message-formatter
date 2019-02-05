import React, { Component } from 'react';
import moment from 'moment';

const timezone = 'Australia/Melbourne';

class Clock extends Component {
  constructor(props) {
    super(props);
    this.state = {
      time: moment().tz(timezone),
    };
  }
  componentDidMount() {
    this.intervalID = setInterval(() => this.tick(), 1000);
  }
  componentWillUnmount() {
    clearInterval(this.intervalID);
  }
  tick() {
    this.setState({
      time: moment().tz(timezone),
    });
  }
  render() {
    const { time } = this.state;
    return (
      <div className="clock">
        <span className="SUF" style={{ fontWeight: 'bold' }}>
          {time.format('HH:mm:ss')}
        </span>
      </div>
    );
  }
}

export default Clock;
