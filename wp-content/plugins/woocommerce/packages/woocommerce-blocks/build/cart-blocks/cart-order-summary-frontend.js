(window.webpackWcBlocksJsonp=window.webpackWcBlocksJsonp||[]).push([[16],{102:function(e,t,a){"use strict";var c=a(12),n=a.n(c),r=a(0),o=a(137),l=a(4),s=a.n(l);a(197);const i=e=>({thousandSeparator:e.thousandSeparator,decimalSeparator:e.decimalSeparator,decimalScale:e.minorUnit,fixedDecimalScale:!0,prefix:e.prefix,suffix:e.suffix,isNumericString:!0});t.a=e=>{let{className:t,value:a,currency:c,onValueChange:l,displayType:u="text",...m}=e;const p="string"==typeof a?parseInt(a,10):a;if(!Number.isFinite(p))return null;const b=p/10**c.minorUnit;if(!Number.isFinite(b))return null;const d=s()("wc-block-formatted-money-amount","wc-block-components-formatted-money-amount",t),f={...m,...i(c),value:void 0,currency:void 0,onValueChange:void 0},x=l?e=>{const t=+e.value*10**c.minorUnit;l(t)}:()=>{};return Object(r.createElement)(o.a,n()({className:d,displayType:u},f,{value:b,onValueChange:x}))}},197:function(e,t){},315:function(e,t){},379:function(e,t,a){"use strict";var c=a(0),n=a(1),r=a(4),o=a.n(r),l=a(102),s=a(10),i=a(32),u=a(2);a(315),t.a=e=>{let{currency:t,values:a,className:r}=e;const m=Object(u.getSetting)("taxesEnabled",!0)&&Object(u.getSetting)("displayCartPricesIncludingTax",!1),{total_price:p,total_tax:b}=a,{receiveCart:d,...f}=Object(i.a)(),x=Object(s.__experimentalApplyCheckoutFilter)({filterName:"totalLabel",defaultValue:Object(n.__)("Total","woocommerce"),extensions:f.extensions,arg:{cart:f}}),O=parseInt(b,10);return Object(c.createElement)(s.TotalsItem,{className:o()("wc-block-components-totals-footer-item",r),currency:t,label:x,value:parseInt(p,10),description:m&&0!==O&&Object(c.createElement)("p",{className:"wc-block-components-totals-footer-item-tax"},Object(c.createInterpolateElement)(Object(n.__)("Including <TaxAmount/> in taxes","woocommerce"),{TaxAmount:Object(c.createElement)(l.a,{className:"wc-block-components-totals-footer-item-tax-value",currency:t,value:O})}))})}},452:function(e,t,a){"use strict";a.r(t);var c=a(0),n=a(379),r=a(41),o=a(32),l=a(10);const s=()=>{const{extensions:e,receiveCart:t,...a}=Object(o.a)(),n={extensions:e,cart:a,context:"woocommerce/cart"};return Object(c.createElement)(l.ExperimentalOrderMeta.Slot,n)};t.default=e=>{let{children:t,className:a=""}=e;const{cartTotals:l}=Object(o.a)(),i=Object(r.getCurrencyFromPriceResponse)(l);return Object(c.createElement)("div",{className:a},t,Object(c.createElement)("div",{className:"wc-block-components-totals-wrapper"},Object(c.createElement)(n.a,{currency:i,values:l})),Object(c.createElement)(s,null))}}}]);