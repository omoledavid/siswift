import{_ as T,l as C,b as i,d as t,f,t as c,e as u,n as b,F as L,h as P,k as v,r as m,o as r}from"../entry/index.wkIW-Yay.js";import{R}from"./ReviewCard.CUfCb_jx.js";const D={components:{ReviewCard:R},data(){return{isLoading:!1,image:C,ID:this.$route.params.id,user:{},tabs:[{label:"Listings"},{label:"reviews"}],activeTab:0,items:[]}},methods:{activateMenu(e){this.activeTab=e},showProduct(e){this.$router.push(`/app/product/${e==null?void 0:e.id}`)},avgRating(e){let o=e.review,l=[];return o.forEach(n=>{l.push(n.rating)}),l.reduce((n,a)=>n+a,0)/l.length||0},getReviewer(e){this.$config.getUserData(e).then(o=>(console.log(o),o.user))},getUser(){this.isLoading=!0,this.$config.getUserData(this.ID).then(e=>{var o;console.log(e),this.user=e.user,this.items=(o=e==null?void 0:e.user)==null?void 0:o.products}).finally(()=>{this.isLoading=!1})}},beforeMount(){this.getUser()},computed:{}},U={key:0},V={key:1,class:"grid lg:grid-cols-3 md:grid-cols-3 grid-cols-1 gap-4"},j={class:"bg-white p-6"},B={class:"flex items-center flex-col mb-3"},N={class:"relative"},S=["src"],F={class:"font-semibold flex items-center mt-3"},M={class:"text-[12px] text-gray-400"},z={class:"mt-3"},E=["href"],I={class:"bg-white p-6 lg:col-span-2 md:col-span-2 col-span-1"},H={class:"w-full flex justify-center mb-4"},O=["onClick"],q={key:0,class:"flex flex-col gap-4"},A={key:1,class:"flex flex-col gap-4"};function G(e,o,l,x,s,n){var p,g,h,_;const a=m("i-icon"),w=m("wxProductCard"),y=m("review-card");return r(),i("div",null,[s.isLoading?(r(),i("span",U," Retrieving Data ")):(r(),i("div",V,[t("div",j,[t("div",B,[t("div",N,[t("img",{src:s.user.image?e.imgUrl+"user/profile/"+s.user.image:s.image,class:"w-[80px] h-[80px] border-2 p-[2px] border-gray-100 rounded-full object-fit object-top"},null,8,S)]),t("h4",F,[f(c(`${s.user.firstname} ${s.user.lastname}`)+" ",1),t("span",null,[u(a,{icon:"ic:round-verified",class:b([(p=s.user)!=null&&p.kv?"text-green-600":"text-gray-300"])},null,8,["class"])])]),t("span",M,c(`joined Siswift ${e.$formatRelativeTime((g=s.user)==null?void 0:g.created_at)}`),1)]),t("div",z,[t("a",{class:"flex items-center text-black1 gap-[5px] text-sm font-semibold",href:`tel:${(h=s.user)==null?void 0:h.mobile}`},[u(a,{icon:"solar:phone-bold",class:"form-icon text-gray-600"}),f(" "+c((_=s.user)==null?void 0:_.mobile),1)],8,E)])]),t("div",I,[t("span",H,[(r(!0),i(L,null,P(s.tabs,(k,d)=>(r(),i("span",{key:d,role:"button",class:b(["capitalize text-[15px] text-center w-full",["border-b-2 font-semibold",s.activeTab===d?"text-primary border-b-primary":"text-gray-500"]]),onClick:J=>n.activateMenu(d)},c(k.label.split("_").join(" ")),11,O))),128))]),s.activeTab==0?(r(),i("div",q,[u(w,{products:s.items,loading:s.isLoading,iconType:"bx:store",isFilterOpen:!0,emptyText:"This user has no listed items yet 😥",hasButton:!1,hasHelper:!1,onViewProduct:n.showProduct},null,8,["products","loading","onViewProduct"])])):v("",!0),s.activeTab==1?(r(),i("div",A,[u(y,{items:s.user.review},null,8,["items"])])):v("",!0)])]))])}const W=T(D,[["render",G]]);export{W as default};
