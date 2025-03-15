import{_ as Q,x as X,b as o,e as l,w as J,r as y,o as n,d as t,F as h,h as m,n as G,t as c,l as x,f as _,u as Y,s as Z,v as $,y as j,z as tt}from"../entry/index.R2o0HwGI.js";const et={props:{productID:Number,isMyStore:{type:Boolean,default:!1}},data(){return{ID:this.$route.params.id,item:{},loading:!1,activeTab:"description",image:X,quantity:1}},methods:{filterFunc(){this.isFilterOpen=!this.isFilterOpen},startChat(){var s,r;this.$router.push(`/app/messages/?user=${(r=(s=this.item)==null?void 0:s.shop)==null?void 0:r.user_id}`)},async onShare(){try{await navigator.share({title:`Checkout my amazing product at ${this.windowOrigin}`,text:"",url:this.productLink})}catch(s){alert(s)}},getProduct(){this.loading=!0,this.$products.getRecord(this.ID).then(s=>{console.log("data from products list:",s),this.item=s.data}).finally(()=>{this.loading=!1})},getUser(){this.$auth.getProfile().then(s=>{console.log(s)})},makeOffer(){this.$router.push(`/app/make-offer/${this.ID}`)},addToCart(){let s={product_id:this.ID,quantity:this.quantity,offer_price:this.item.base_price};this.$user.addToCart(s).then(r=>{console.log(r),this.$router.push("/app/my-cart")}),console.log(s)},addToWishlist(){let s={product_id:this.ID,quantity:this.quantity,offer_price:this.item.base_price};this.$user.addToWishlist(s).then(r=>{console.log(r),this.$router.push("/app/my-favourites")}),console.log(s)},removeFromCart(s){this.$user.removeFromCart(s).then(r=>{console.log(r)})}},beforeMount(){this.getProduct(),this.getUser()},watch:{productID:{handler(s){s&&(this.ID=s,this.getProduct())},immediate:!0}},computed:{user(){return this.$store.getters["auth/getUser"]},isMyProduct(){return this.user.id==this.item.shop.user_id},windowOrigin(){return window.location.origin},productLink(){return`${this.windowOrigin}/app/product/${this.ID}`}}},p=s=>(j("data-v-1a4518b2"),s=s(),tt(),s),st={class:"body-content w-full lg:page-bg md:page-bg"},it={class:"flex justify-between gap-12 mb-3"},rt={class:"grid grid-cols-3 w-full gap-4"},ot={class:"flex gap-4"},lt=["src"],nt={class:"grid grid-cols-4 gap-3 mb-3"},at=["src"],ct={class:"flex gap-2 items-center",role:""},dt={key:0,role:"button",class:"bg-accent p-2 rounded-md whitespace-nowrap block text-primary text-xs font-semibold"},pt={class:"mt-4 flex flex-col gap-1"},ut={class:"text-[13px] block"},ht=p(()=>t("b",null,"Brand:",-1)),mt={class:"font-semibold"},xt={class:"font-bold text-xl text-primary"},_t={class:"flex flex-col gap-[5px]"},gt={class:"text-[12px] block w-fit"},ft={class:"flex flex-col gap-2"},bt={class:"text-[13px] block bg-primary text-white w-fit rounded-sm px-[6px] py-[2px] block"},yt={class:"text-[13px] block"},vt=p(()=>t("b",null,"RAM:",-1)),kt={class:"flex gap-[7px] text-sm items-center"},wt=p(()=>t("b",null,"Color:",-1)),Ct=p(()=>t("span",{class:"flex gap-2"},null,-1)),Tt={class:"border-b border-b-gray-400 w-full flex gap-4 justify-center"},It=["onClick"],St={class:"mt-2"},Dt={key:0},Mt=p(()=>t("h4",{class:"font-semibold text-[13px]"},"Description",-1)),Ft=["innerHTML"],Pt={key:1},Ot={class:"flex flex-col gap-3"},qt={class:"text-[14px] font-semibold"},Nt={class:"flex items-start gap-2"},Ut=["src"],At=p(()=>t("p",{class:"text-sm"},"comment",-1)),Lt={class:"text-xs flex gap-[2px]"},zt=p(()=>t("hr",{class:"my-[4px]"},null,-1)),Bt={class:"text-[13px] block"},Vt={class:"text-[13px] flex gap-[3px] items-center"},Wt={key:0,class:"text-[13px] block font-semibold"},Et=p(()=>t("hr",{class:"my-4"},null,-1)),Ht={key:1,class:"flex justify-between items-center"},Rt={class:"font-bold text-primary"},Jt=p(()=>t("button",{class:"brand-btn-md brand-primary"},"Edit",-1)),Gt={key:2,class:"flex lg:flex-row md:flex-row flex-col justify-between gap-3"},Kt={class:"flex lg:flex-row md:flex-row flex-col gap-3"},Qt={class:"flex gap-2"};function Xt(s,r,Yt,Zt,i,a){const d=y("el-skeleton-item"),g=y("i-icon"),K=y("el-skeleton");return n(),o("div",st,[l(K,{loading:i.loading,animated:""},{template:J(()=>[t("div",null,[l(d,{variant:"image",style:{height:"200px","margin-bottom":"20px","border-radius":"10px"}}),t("div",it,[t("div",rt,[(n(),o(h,null,m(3,f=>t("div",{class:"",key:f},[l(d,{variant:"image",style:{height:"50px","border-radius":"4px"}})])),64))]),t("span",ot,[l(d,{variant:"text",style:{height:"50px",width:"50px","border-radius":"10px"}}),l(d,{variant:"text",style:{height:"50px",width:"50px","border-radius":"10px"}})])]),t("div",null,[l(d,{variant:"text",style:{height:"10px","border-radius":"10px"}}),l(d,{variant:"text",style:{height:"10px","border-radius":"10px"}}),l(d,{variant:"text",style:{height:"10px","border-radius":"10px"}}),l(d,{variant:"text",style:{height:"10px","border-radius":"10px"}}),l(d,{variant:"text",style:{height:"10px","border-radius":"10px"}}),l(d,{variant:"text",style:{height:"10px","border-radius":"10px"}}),l(d,{variant:"text",style:{height:"10px","border-radius":"10px"}})])])]),default:J(()=>{var f,v,k,w,C,T,I,S,D,M,F,P,O,q,N,U,A,L,z,B,V,W,E;return[t("div",null,[t("div",null,[t("img",{src:s.imgUrl+"product/"+((f=i.item)==null?void 0:f.main_image),alt:"",role:"button",class:"h-[300px] w-full rounded-md object-contain object-center border border-primary"},null,8,lt),t("div",{class:G(["mt-4",{"flex justify-between":!a.isMyProduct}])},[t("div",nt,[(n(!0),o(h,null,m((v=i.item)==null?void 0:v.product_images,e=>(n(),o("img",{key:e==null?void 0:e.id,src:s.imgUrl+"product/"+(e==null?void 0:e.image),alt:"",role:"button",class:"h-[40px] w-[60px] rounded-[4px] object-cover object-center"},null,8,at))),128))]),t("span",ct,[a.isMyProduct?(n(),o("span",dt,c((k=i.item)!=null&&k.isfeatured?"Check Sponsored Analysis":"Sponsor Listing"),1)):x("",!0),a.isMyProduct?x("",!0):(n(),o("span",{key:1,onClick:r[0]||(r[0]=(...e)=>a.addToWishlist&&a.addToWishlist(...e)),class:"bg-accent p-2 rounded-md text-primary text-lg",role:"button"},[l(g,{icon:"ph:heart-fill"})])),t("span",{role:"button",onClick:r[1]||(r[1]=(...e)=>a.onShare&&a.onShare(...e)),class:"bg-accent p-2 rounded-md text-primary text-lg"},[l(g,{icon:"ic:baseline-share"})])])],2),t("div",pt,[t("span",ut,[ht,_(" "+c((C=(w=i.item)==null?void 0:w.brand)==null?void 0:C.name),1)]),t("h4",mt,c(`${(T=i.item)==null?void 0:T.name} ${(I=i.item)==null?void 0:I.model}`),1),t("h4",xt,c(`${s.$currencyFormat((S=i.item)==null?void 0:S.base_price)}`),1),t("div",_t,[(n(!0),o(h,null,m(JSON.parse((D=i.item)==null?void 0:D.bulk_price),(e,u)=>(n(),o("div",{class:"bg-accent p-[6px] w-fit",key:u},[t("span",gt,[t("b",null,c(`From ${e.qty} pieces:`),1),_(" "+c(`${s.$currencyFormat(e.price)}/piece`),1)])]))),128))]),t("div",ft,[t("span",bt,c((M=i.item)==null?void 0:M.condition),1),t("span",yt,[vt,_(" "+c((F=i.item)==null?void 0:F.ram),1)]),t("span",kt,[wt,t("span",{class:"block w-4 h-4 p-2 cursor-pointer rounded-full ring-2 ring-offset-2 ring-primary",style:Y({backgroundColor:(P=i.item)==null?void 0:P.colour})},null,4)]),Ct]),t("div",null,[t("span",Tt,[(n(),o(h,null,m(["description","reviews"],(e,u)=>t("span",{key:u,role:"button",class:G(["capitalize w-full text-center text-sm",{"border-b text-primary border-b-primary font-semibold":i.activeTab===e}]),onClick:b=>i.activeTab=e},c(e),11,It)),64))]),t("div",St,[i.activeTab=="description"?(n(),o("div",Dt,[t("span",null,[Mt,t("div",{class:"text-xs",innerHTML:(O=i.item)==null?void 0:O.description},null,8,Ft)])])):x("",!0),i.activeTab=="reviews"?(n(),o("div",Pt,[t("div",Ot,[(n(!0),o(h,null,m((q=i.item)==null?void 0:q.reviews,e=>{var u,b,H,R;return n(),o("div",{key:e==null?void 0:e.id},[t("h6",qt,c(`${(u=e==null?void 0:e.user)==null?void 0:u.firstname} ${(b=e==null?void 0:e.user)==null?void 0:b.lastname}`),1),t("div",Nt,[t("span",null,[t("img",{src:(H=e==null?void 0:e.user)!=null&&H.image?s.imgUrl+"user/profile/"+((R=e==null?void 0:e.user)==null?void 0:R.image):i.image,class:"w-[35px] h-[35px] border-2 p-[2px] border-gray-100 rounded-full object-fit object-top"},null,8,Ut)]),t("div",null,[At,t("span",Lt,[l(g,{icon:"mingcute:star-fill",class:"text-secondary text-xs"}),_(" 4 ")])])])])}),128))])])):x("",!0)])]),zt,t("div",null,[t("span",Bt,"Address: "+c((N=i.item)==null?void 0:N.location),1),t("span",Vt,[l(g,{icon:"tabler:location-filled",class:"text-secondary text-sm"}),_(" "+c(((U=i.item)==null?void 0:U.state)==="AkwaIbom"?`${(A=i.item)==null?void 0:A.lga}, Akwa Ibom`:`${(L=i.item)==null?void 0:L.lga}, ${(z=i.item)==null?void 0:z.state}`),1)]),(B=i.item)!=null&&B.shop?(n(),o("span",Wt,"Store Name: "+c((W=(V=i.item)==null?void 0:V.shop)==null?void 0:W.name),1)):x("",!0),Et,a.isMyProduct?(n(),o("div",Ht,[t("h4",Rt,c(s.$currencyFormat((E=i.item)==null?void 0:E.base_price)),1),Jt])):(n(),o("div",Gt,[t("span",Kt,[t("button",{class:"brand-btn-md brand-outline",onClick:r[2]||(r[2]=(...e)=>a.makeOffer&&a.makeOffer(...e))}," Make an offer "),t("button",{class:"brand-btn-md brand-outline",onClick:r[3]||(r[3]=(...e)=>a.startChat&&a.startChat(...e))}," Start a chat ")]),t("div",Qt,[Z(t("input",{type:"text","onUpdate:modelValue":r[4]||(r[4]=e=>i.quantity=e),class:"input w-[100px]"},null,512),[[$,i.quantity]]),t("button",{class:"brand-btn-md brand-primary whitespace-nowrap",onClick:r[5]||(r[5]=(...e)=>a.addToCart&&a.addToCart(...e))},"Add to Cart")])]))])])])])]}),_:1},8,["loading"])])}const jt=Q(et,[["render",Xt],["__scopeId","data-v-1a4518b2"]]);export{jt as default};
