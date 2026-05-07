//simple password requirements validation
(function(){
  //regex rules
  const rules = {
    len: v => v.length >= 8,
    lower: v => /[a-z]/.test(v),
    upper: v => /[A-Z]/.test(v),
    num: v => /\d/.test(v),
    spec: v => /[^A-Za-z0-9]/.test(v)
  };

  //update ui state for rules
  function updateRuleItems(root, value){
    Object.keys(rules).forEach(key => {
      const li = root.querySelector(`li[data-rule="${key}"]`);
      if(!li) return;
      const ok = rules[key](value);
      li.classList.toggle('valid', ok);
      li.classList.toggle('invalid', !ok);
      li.classList.toggle('text-green-500', ok);
      li.classList.toggle('text-muted', !ok);
    });
  }

  //public init api
  window.initPasswordRequirements = function(passSel, confirmSel, submitSel, formSel){
    const pass = document.querySelector(passSel);
    const confirm = document.querySelector(confirmSel);
    const submit = document.querySelector(submitSel);
    const form = document.querySelector(formSel);
    if(!pass || !confirm || !submit) return;

    const reqRoot = pass.parentElement.querySelector('.pwd-req');

    function recompute(){
      const v = pass.value || '';
      //rules state
      updateRuleItems(reqRoot, v);
      const allOk = Object.keys(rules).every(k => rules[k](v));
      //confirm match
      const match = v !== '' && v === (confirm.value || '');

      //toggle input classes
      pass.classList.toggle('!border-green-500', allOk);
      pass.classList.toggle('!border-red-500', !allOk && v.length > 0);
      confirm.classList.toggle('!border-green-500', match);
      confirm.classList.toggle('!border-red-500', !match && confirm.value.length > 0);

      //enable submit when all ok and match
      if(submit){
        submit.disabled = !(allOk && match);
      }
    }

    pass.addEventListener('input', recompute);
    confirm.addEventListener('input', recompute);

    if(form){
      form.addEventListener('submit', function(e){
        //prevent submit if invalid
        const v = pass.value || '';
        const allOk = Object.keys(rules).every(k => rules[k](v));
        const match = v !== '' && v === (confirm.value || '');
        if(!(allOk && match)){
          e.preventDefault();
          recompute();
        }
      });
    }

    //initial state
    recompute();
  };
})();
