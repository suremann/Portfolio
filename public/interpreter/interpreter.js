document.onkeypress = function (e) {
  e = e || window.event;
  // use e.keyCode
  if(e.keyCode === 13){ //Enter key
    var input = document.getElementById('terminal-input');
    output(">> " + input.value);
    try{
      output(evaluate(input.value));
    }catch(e){}
    input.value = '';
  }
};
//Anytime mouse is clicked, focus the input
document.onclick = function (e) {
  e = e || window.event;
  document.getElementById('terminal-input').focus();
};

var regex = {
  'c_or_p': /\( *([C|P]) *(\d+) *(\d+) *\)/ig,
  'define': /^ *\( *DEFINE *(\w+) *([-+*^/\w()! .]+) *\) *$/i,
  'var_exp': /\( *(\w+!?) *\)/g,
  'eval_exp': /\( *(\d+) *([-+*\/]) *(\d+) *\)/g,
  'reset_exp': /^ *\(? *__reset__ *\)? *$/i,
  'env_exp': /^ *\(? *__env__ *\)? *$/i,
  'fact_exp': /\( *(\d+) *! *\)/g
};

var env = {};

function evaluate(exp){
  if(exp.match(regex.reset_exp)){
    env = {};
    exit(' ', true);
  }
  if(exp.match(regex.env_exp)){
    exit(JSON.stringify(env));
  }
  //Find any var expressions and replace with their value stored in the env
  exp = exp.replace(regex.var_exp, function(match, g1){
    if(g1.toUpperCase() === '__ENV__'){
      exit(JSON.stringify(env));
      //Check if only expression, if so return otherwise throw error
    }else{
      if(env[g1] !== undefined)
        return env[g1];
      return match;
    }
  });
  exp = exp.replace(regex.eval_exp, function(match, g1, g2, g3){
    return eval(match);
  });
  //Find any factorials and replace with their value.
  exp = exp.replace(regex.fact_exp, function(match, g1) {
    return pad(fact(g1));
  });
  //Find any C or P expressions and replace them with their evaluated value.
  exp = exp.replace(regex.c_or_p, function(match, g1, g2, g3) {
    return count_c_or_p(g1, g2, g3);
  });

  var match = exp.match(regex.define);
  if(match){
    var val = evaluate(match[2]);
    env[match[1]] = val;
    return match[1] + " => " + val;
  }

  try{
    return eval(exp);
  }catch(e){
    output(e + ' in expression: ' + exp);
  }
}

function pad(str, padding){
  if(padding === undefined) padding = " ";
  return padding + str + padding;
}

function exit(message, clear){
  if(clear === undefined) clear = false;
  output(message, clear);
  throw '';
}

function output(content, reset){
  if(reset === undefined) reset = false;
  if(content) {
    var out = document.getElementById('terminal-output');
    if(reset) out.innerHTML = '';
    out.innerHTML += content + "<br>";
    out.scrollTop = out.scrollHeight;
  }
}

function count_c_or_p(op, n, k){
  if (op.toUpperCase() === 'P'){
    return count_perms(n, k);
  }
  else if(op.toUpperCase() === 'C'){
    return count_combs(n, k);
  }
}

function count_perms(n, k){
  return fact(n) / fact(n - k);
}

function count_combs(n, k){
  return fact(n) / (fact(n - k) * fact(k));
}

function fact(n){
  return n <= 1 ? 1 : n * fact(n - 1);
}