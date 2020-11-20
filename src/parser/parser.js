import moment from 'moment-timezone';

function parse(file) {
  const events = mergeEvents(lines(file));
  const eventArray = [];
  for (const key in events) {
      eventArray.push(events[key]);
  }
  return eventArray.sort((a, b) => {
    if (a.dateTime.isAfter(b.dateTime)) { return -1 }
    if (a.dateTime.isBefore(b.dateTime)) {return 1 }
    return 0;
  });
}

function lines(file) {
  let l = [];
  file.split('\n').forEach(line => {
    line = line.trim();
    if (line && line !== '[HTML BUFFER]') {
      try {
        l.unshift(event(line));
      } catch (err) {
        // swallow it
      }
    }
  });
  return l;
}

function mergeEvents(events) {
  const map = {};
  events.forEach(e => {
    const { eventId, eventType } = e;
    if (!map.hasOwnProperty(eventId) && eventType === '@@ALERT') {
      e.startTime = e.dateTime.format('HH:mm:ss');
      map[eventId] = e;
    } else if (map.hasOwnProperty(eventId)){
      map[eventId] = merge(map[eventId], e);
    }
  });
  return map;
}

function merge(o, n) {
  if (n.eventType === '@@ALERT') {
    const { brigades } = o;
    const { unit, dateTime } = n;
    const i = brigades.findIndex(brig => {
      return brig.unit === unit || brig.unit === 'C' + unit
    });
    if (i > -1) {
      brigades[i].dateTime = dateTime;
    }
    n.brigades.forEach(brig => {
      if (!brigades.find(b => b.code === brig.code)) {
        brigades.push(brig);
      }
    });
    return { ...o, ...n, brigades };
  } else { // update
    const brigades = o.brigades;
    const { unit, msg, dateTime } = n;
    if (brigades) {
      const i = brigades.findIndex(brig => {
        return brig.code === unit || brig.code === 'C' + unit
      });
      if (i > -1 && (msg.startsWith('STOP') || msg.startsWith('CANCEL'))) {
        brigades[i].cancelled = true;
    }}
    return { ...o, dateTime, brigades, updates: [...o.updates, n] };
  }
}

function event(line) {
  let words = line.split(' ');
  if (words === undefined) {
    return null
  }
  const dateTime = moment(words[1] + ' ' + words[2], 'HH:mm:ss DD-MM-YY')
    .tz('Australia/Melbourne');
  const eventType = words[6];
  words = words.slice(7);
  if (eventType === '@@ALERT') {
    let buf = [];
    let word = words.shift();
    while (word && !/\(\d+\)/.test(word)) {
      buf.push(word);
      word = words.shift();
    }
    buf.push(word);
    const msg = buf.join(' ');
    word = words.shift();
    let responseRequired;
    if (/^[AFPRS]+$/.test(word)) { // check response agency is valid
      responseRequired = word;
      word = words.shift();
    } else {
      responseRequired = '';
    }
    let brigades = [];
    while (word && !/F\d+/.test(word)) {
      brigades.push({
        code: word,
        dateTime,
        cancelled: false,
      });
      word = words.shift();
    }
    const eventId = word;
    word = words.shift();
    const notified = '<SPAN' === word;
    const unit = word && !notified ? word.replace('[', '').replace(']', '') : '';
    
    return {
      dateTime,
      eventType,
      msg,
      responseRequired,
      brigades,
      eventId,
      updates: [],
      unit,
      notified,
    };
  } else { // update
    const eventId = words[1];
    const msg = words.slice(3, words.length - 1).join(' ');
    const unit = words[words.length - 1].replace('[', '').replace(']', '');
    return {
      eventId,
      msg,
      dateTime,
      eventType,
      unit,
    };
  }
}

export default parse;
