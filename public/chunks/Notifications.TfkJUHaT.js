import{_ as n,L as c,b as a,d as o,e as f,r,o as l}from"../entry/index.wkIW-Yay.js";const _={components:{wxNotification:c},data(){return{notifications:[]}},methods:{list(){this.$user.getNotifications().then(t=>{console.log(t),this.notifications=t.notifications})}},beforeMount(){this.list()}},d={class:"bg-white p-6 rounded-lg"},h={class:"mt-3"},m=o("h4",{class:"mb-3 font-semibold"},"Notifications",-1);function p(t,u,x,N,s,i){const e=r("wx-notification");return l(),a("div",d,[o("div",h,[m,f(e,{items:s.notifications,"onRefresh:notifications":i.list,class:"flex flex-col gap-4"},null,8,["items","onRefresh:notifications"])])])}const g=n(_,[["render",p]]);export{g as default};
