(this.webpackJsonp=this.webpackJsonp||[]).push([["transaction-mail-extender"],{MVdG:function(e,t){e.exports='{% block eins_und_eins_config_mapping %}\n    <div>\n        <label>{{ label }}</label>\n        <table class="sw-data-grid__table">\n            <tbody class="sw-data-grid__body">\n            <template v-for="state in states">\n                <tr class="sw-data-grid__row">\n                    <td class="sw-data-grid__cell">{{ state.value }}</td>\n                    <td class="sw-data-grid__cell">\n                        <sw-single-select\n                                size="medium"\n                                :key="state.key"\n                                :options="options"\n                                class="sw-condition-type-select__select"\n                                placeholder="Schema.Org states"\n                                @change="state.onSelect"\n                                :value="state.selection"\n                        ></sw-single-select>\n                    </td>\n                </tr>\n            </template>\n            </tbody>\n        </table>\n    </div>\n{% endblock %}\n'},fuXc:function(e,t,a){"use strict";a.r(t);var n=a("MVdG"),s=a.n(n);const{Criteria:r}=Shopware.Data;Shopware.Component.register("eins-und-eins-config-mapping",{inject:["repositoryFactory"],template:s.a,created(){this.initStates()},methods:{initStates(){const e=this.getStateValues(),t={};this.searchForAll("state_machine").then(e=>{const t={};return e.forEach(e=>{const a=e.technicalName,n=e.name;t[e.id]={key:a,value:n}}),t}).then(a=>{this.searchForAll("state_machine_state").then(n=>{n.forEach(n=>{const s=a[n.stateMachineId].key+"-"+n.technicalName,r=a[n.stateMachineId].value+": "+n.name,l={key:s,value:r,selection:e[s]||"",onSelect:e=>{this.setChoice(e,s)}};t[s]=l}),this.$data.states=t})})},searchForAll(e){const t=new r;t.setLimit(100);return this.repositoryFactory.create(e).search(t,Shopware.Context.api)},setChoice(e,t){this.$data.states[t].selection=e,this.setStateValue(t,e)},getStateValues(){const e=this.$parent.$parent.$parent.actualConfigData;return e?e.null[this.$attrs.name]||{}:this.$parent.$parent.$parent.$parent.actualConfigData.null[this.$attrs.name]||{}},setStateValue(e,t){const a=this.getStateValues();a[e]=t,this.$parent.$parent.$parent.actualConfigData?this.$parent.$parent.$parent.actualConfigData.null[this.$attrs.name]=a:this.$parent.$parent.$parent.$parent.actualConfigData.null[this.$attrs.name]=a}},data(){return{states:{},options:[{label:"OrderCancelled",value:"OrderCancelled"},{label:"OrderDelivered",value:"OrderDelivered"},{label:"OrderInTransit",value:"OrderInTransit"},{label:"OrderPaymentDue",value:"OrderPaymentDue"},{label:"OrderPickupAvailable",value:"OrderPickupAvailable"},{label:"OrderProblem",value:"OrderProblem"},{label:"OrderProcessing",value:"OrderProcessing"},{label:"OrderReturned",value:"OrderReturned"}],label:this.$attrs.label}}})}},[["fuXc","runtime"]]]);