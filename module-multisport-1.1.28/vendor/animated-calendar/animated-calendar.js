/*!
 * animated-calendar (mini) — vendor local
 * Minimal monthly calendar with soft transitions.
 */
(function(global){
  function pad(n){return (n<10?'0':'')+n;}
  function ymd(d){return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate());}
  function startOfMonth(y,m){return new Date(y,m,1);}
  function endOfMonth(y,m){return new Date(y,m+1,0);}
  function prevMonth(y,m){var d=new Date(y,m,1); d.setMonth(d.getMonth()-1); return d;}
  function nextMonth(y,m){var d=new Date(y,m,1); d.setMonth(d.getMonth()+1); return d;}
  function weekdayMon0(d){ // Monday=0
    var w=d.getDay(); // Sun=0
    return (w+6)%7;
  }

  function render(el, state){
    el.innerHTML='';
    el.classList.add('ac');

    var header=document.createElement('div');
    header.className='ac-header';

    var btnPrev=document.createElement('button');
    btnPrev.type='button'; btnPrev.className='ac-btn'; btnPrev.textContent='‹';
    btnPrev.addEventListener('click', function(){
      var d=prevMonth(state.y,state.m);
      state.y=d.getFullYear(); state.m=d.getMonth();
      animate(el,function(){ render(el,state); });
    });

    var btnNext=document.createElement('button');
    btnNext.type='button'; btnNext.className='ac-btn'; btnNext.textContent='›';
    btnNext.addEventListener('click', function(){
      var d=nextMonth(state.y,state.m);
      state.y=d.getFullYear(); state.m=d.getMonth();
      animate(el,function(){ render(el,state); });
    });

    var title=document.createElement('div');
    title.className='ac-title';
    var months=['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    title.textContent = months[state.m]+' '+state.y;

    header.appendChild(btnPrev); header.appendChild(title); header.appendChild(btnNext);
    el.appendChild(header);

    var dow=document.createElement('div');
    dow.className='ac-dow';
    ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'].forEach(function(x){
      var c=document.createElement('div'); c.textContent=x; dow.appendChild(c);
    });
    el.appendChild(dow);

    var grid=document.createElement('div');
    grid.className='ac-grid';

    var first=startOfMonth(state.y,state.m);
    var last=endOfMonth(state.y,state.m);
    var offset=weekdayMon0(first);
    var days=last.getDate();

    // fill leading blanks
    for(var i=0;i<offset;i++){
      var cell=document.createElement('div'); cell.className='ac-cell ac-off'; grid.appendChild(cell);
    }

    for(var d=1; d<=days; d++){
      var date=new Date(state.y,state.m,d);
      var key=ymd(date);
      var cell=document.createElement('button');
      cell.type='button';
      cell.className='ac-cell';
      cell.setAttribute('data-date', key);

      var top=document.createElement('div');
      top.className='ac-day';
      top.textContent = d;
      cell.appendChild(top);

      var items = (state.items && state.items[key]) ? state.items[key] : [];
      if(items.length){
        var dots=document.createElement('div');
        dots.className='ac-dots';
        items.slice(0,3).forEach(function(it){
          var dot=document.createElement('span'); dot.className='ac-dot'; dots.appendChild(dot);
        });
        cell.appendChild(dots);

        var label=document.createElement('div');
        label.className='ac-mini';
        label.textContent = items[0].label;
        cell.appendChild(label);
      }

      cell.addEventListener('click', function(){
        var k=this.getAttribute('data-date');
        if(typeof state.onSelect === 'function') state.onSelect(k);
      });

      grid.appendChild(cell);
    }

    el.appendChild(grid);

    if(state.footer){
      var f=document.createElement('div');
      f.className='ac-footer';
      f.textContent=state.footer;
      el.appendChild(f);
    }
  }

  function animate(el, cb){
    el.classList.add('ac-anim');
    setTimeout(function(){
      cb();
      setTimeout(function(){ el.classList.remove('ac-anim'); }, 200);
    }, 60);
  }

  global.AnimatedCalendar = {
    mount: function(el, options){
      options = options || {};
      var now=new Date();
      var state = {
        y: options.year || now.getFullYear(),
        m: (typeof options.month==='number') ? options.month : now.getMonth(),
        items: options.items || {},
        onSelect: options.onSelect || null,
        footer: options.footer || ''
      };
      render(el, state);
      return state;
    }
  };
})(window);
