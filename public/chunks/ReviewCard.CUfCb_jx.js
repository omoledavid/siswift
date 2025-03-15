import{_ as E,l as V,r as m,o as l,b as o,d as s,e as p,k as h,F as f,h as v,t as u,w as _,f as g,v as k,x as b,n as w,E as D}from"../entry/index.wkIW-Yay.js";const S={props:{items:Array},data(){return{image:V,visible:!1,content:"",review_id:"",editing:null,replyContent:"",reviewers:{}}},methods:{async fetchReviewers(){const n=this.items.map(e=>(console.log(e),this.$config.getUserData(`${e.user_id}`).then(d=>{console.log(d,"testing"),this.reviewers[e.user_id]=d.user}).catch(d=>{console.log(d)})));try{await Promise.all(n)}catch{this.error="An error occurred while fetching reviewer data."}finally{this.loading=!1}},handleCommand(n,e){if(n=="reply")return this.reply(e);this.deleteReview(e)},completeAction(n,e){if(console.log(n),n=="edit")return this.editing=e.id;this.deleteRecord(e)},deleteRecord(n){this.$user.deleteReviewReply(n.id).then(e=>(this.$emit("refresh"),e))},deleteReview(n){console.log(n),this.$user.deleteReview(n.id).then(e=>(this.$emit("refresh"),e))},cancelEditing(){this.editing=null},reply(n){this.visible=!this.visible,this.review_id=n.id},completeEdit(n){let e={content:n.content,method:"_put"};this.$user.editReviewReply(e,this.editing).then(d=>(this.editing=null,this.$emit("refresh"),d))},onSubmit(){let n={review_id:this.review_id,content:this.content};this.$user.replyReview(n).then(e=>(this.visible=!this.visible,this.$emit("refresh"),e))}},mounted(){this.fetchReviewers()},computed:{user(){return this.$store.getters["auth/getUser"]}}},z={class:"flex flex-col"},A={key:0,class:"flex flex-col justify-center items-center"},N={class:"bg-accent block h-[100px] w-[100px] text-primary rounded-full flex justify-center items-center"},B=s("h5",null,"User has no review",-1),F={class:"flex items-center justify-between"},M={class:"flex gap-1 items-center"},T=["src"],q={key:0,class:"text-[16px] font-semibold"},L={key:0},P={class:"el-dropdown-link flex items-center"},G={class:"flex items-start gap-2"},H={class:"text-sm"},I={class:"text-xs flex gap-[2px]"},J={class:"flex gap-4 mt-2"},K=["onClick"],O={class:"flex gap-2 flex-col mt-3"},Q={class:"flex gap-1 mb-1 items-center"},W=["src"],X={class:"font-semibold text-[14px]"},Y=["onUpdate:modelValue"],Z={key:1,class:"text-[13px]"},$={key:2,class:"mt-1"},j={key:0,class:"flex gap-3 items-center"},ee=["onClick"],te={key:1,class:"flex gap-3 items-center"},se=["onClick"],ie={class:"mb-6"},ne=s("label",{for:""},"Content",-1),re=s("div",{class:"text-center"},[s("button",{type:"submit",class:"brand-btn-md bg-primary brand-primary w-7/12"}," Submit ")],-1);function le(n,e,d,oe,r,c){const x=m("i-icon"),y=m("el-dropdown-item"),R=m("el-dropdown-menu"),C=m("el-dropdown"),U=m("vDialog");return l(),o("div",z,[d.items.length==0?(l(),o("div",A,[s("span",N,[p(x,{icon:"mdi:rate-review",class:"text-[60px]"})]),B])):h("",!0),(l(!0),o(f,null,v(d.items,t=>(l(),o("div",{key:t==null?void 0:t.id,class:"border-b border-b-gray-200 py-2"},[s("div",F,[s("div",M,[s("span",null,[s("img",{src:r.reviewers[t.user_id]&&r.reviewers[t.user_id].image?n.imgUrl+"user/profile/"+r.reviewers[t.user_id].image:r.image,class:"w-[35px] h-[35px] border-2 p-[2px] border-gray-100 rounded-full object-fit object-top"},null,8,T)]),r.reviewers[t.user_id]?(l(),o("h6",q,u(r.reviewers[t.user_id]?`${r.reviewers[t.user_id].firstname} ${r.reviewers[t.user_id].lastname}`:"User not found"),1)):h("",!0)]),c.user.id==(t==null?void 0:t.reviewed_user_id)?(l(),o("div",L,[p(C,{trigger:"click",placement:"bottom-end",onCommand:i=>c.handleCommand(i,t)},{dropdown:_(()=>[p(R,{class:"w-[150px]"},{default:_(()=>[p(y,{command:"reply",class:"capitalize block text-[13px] hover:bg-accent py-[5px] px-[8px] rounded-sm"},{default:_(()=>[g("Reply")]),_:1}),p(y,{command:"deleteRecord",class:"capitalize block hover:bg-accent text-[13px] py-[5px] px-[8px] rounded-sm text-red-600"},{default:_(()=>[g("Delete")]),_:1})]),_:1})]),default:_(()=>[s("span",P,[p(x,{icon:"pepicons-pencil:dots-y",width:"20px"})])]),_:2},1032,["onCommand"])])):h("",!0)]),s("div",G,[s("div",null,[s("p",H,u(t==null?void 0:t.content),1),s("span",I,[p(x,{icon:"mingcute:star-fill",class:"text-secondary text-xs"}),g(" "+u(t==null?void 0:t.rating),1)])])]),s("div",J,[s("span",{class:"underline text-primary text-xs font-semibold",role:"button",onClick:i=>n.viewRecord(t)},"Replies",8,K)]),s("div",O,[(l(!0),o(f,null,v(t==null?void 0:t.replies,i=>(l(),o("span",{class:"text-sm",key:i==null?void 0:i.id},[s("div",Q,[s("img",{src:i.image?n.imgUrl+"user/profile/"+i.user.image:r.image,class:"w-[20px] h-[20px] border-1 p-[1px] border-gray-100 rounded-full object-fit object-top"},null,8,W),s("span",X,u(`${i.user.firstname} ${i.user.lastname}`),1)]),r.editing==i.id?k((l(),o("textarea",{key:0,"onUpdate:modelValue":a=>i.content=a,class:"input py-1"}," ",8,Y)),[[b,i.content]]):(l(),o("span",Z,u(i.content),1)),c.user.id==(t==null?void 0:t.reviewed_user_id)?(l(),o("span",$,[r.editing==i.id?(l(),o("span",j,[s("span",{role:"button",onClick:a=>c.completeEdit(i),class:w(["text-[12px] font-semibold text-primary"])}," Finish Editing ",8,ee),s("span",{role:"button",onClick:e[0]||(e[0]=(...a)=>c.cancelEditing&&c.cancelEditing(...a)),class:w(["text-[12px] font-semibold text-red-600"])}," Cancel ")])):(l(),o("span",te,[(l(),o(f,null,v(["edit","delete"],a=>s("span",{role:"button",onClick:de=>c.completeAction(a,i),class:w(["text-[12px] font-semibold capitalize",a=="edit"?"text-amber-600":"text-red-600"]),key:a},u(a),11,se)),64))]))])):h("",!0)]))),128))]),p(U,{visible:r.visible,"onUpdate:visible":e[3]||(e[3]=i=>r.visible=i),pt:{root:"border-none",mask:{style:"backdrop-filter: blur(2px)"}},modal:"",header:"Reply Review",style:{width:"30rem"}},{default:_(()=>[s("form",{onSubmit:e[2]||(e[2]=D((...i)=>c.onSubmit&&c.onSubmit(...i),["prevent"]))},[s("div",ie,[ne,k(s("textarea",{name:"","onUpdate:modelValue":e[1]||(e[1]=i=>r.content=i),class:"input",id:""},null,512),[[b,r.content]])]),re],32)]),_:1},8,["visible","pt"])]))),128))])}const ae=E(S,[["render",le]]);export{ae as R};
