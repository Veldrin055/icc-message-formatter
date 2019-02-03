import moment from 'moment';

function parse(file) {
  const events = mergeEvents(lines(file));
  const eventArray = [];
  for (const key in events) {
    eventArray.push(events[key]);
  }
  return eventArray;
}

function lines(file) {
  let l = [];
  file.split('\n').forEach(line => {
    line = line.trim();
    if (line && line !== '[HTML BUFFER]') {
      l.unshift(event(line));
    }
  });
  return l;
}

function mergeEvents(events) {
  const map = {};
  events.forEach(e => {
    const { eventId } = e;
    if (!map.hasOwnProperty(eventId)) {
      map[eventId] = e;
    } else {
      map[eventId] = merge(map[eventId], e);
    }
  });
  return map;
}

function merge(o, n) {
  if (n.eventType === '@@ALERT') {
    const brigades = [...o.brigades, ...n.brigades];
    return { ...o, ...n, brigades };
  } else {
    return { ...o, updates: [...o.updates, n] };
  }
}

function event(line) {
  let words = line.split(' ');
  const dateTime = moment(words[1] + ' ' + words[2], 'HH:mm:ss DD-MM-YY');
  const eventType = words[6];
  words = words.slice(7);
  if (eventType === '@@ALERT') {
    let buf = [];
    let word = words.shift();
    while (word && !/\(\d+\)/.test(word)) {
      buf.push(word);
      word = words.shift();
    }
    const msg = buf.join(' ');
    const responseRequired = words.shift(); // check contains only proper chars
    let brigades = [];
    word = words.shift();
    while (word && !/F\d+/.test(word)) {
      brigades.push(word);
      word = words.shift();
    }
    const eventId = word;

    return {
      dateTime,
      eventType,
      msg,
      responseRequired,
      brigades,
      eventId,
      updates: [],
    };
  } else {
    // update
    const eventId = words[1];
    const msg = words.slice(3, words.length - 1).join(' ');
    return {
      eventId,
      msg,
      dateTime,
      eventType,
    };
  }
}

export default parse;
