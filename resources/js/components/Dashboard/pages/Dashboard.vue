<template>
  <div>
      <div class="row g-5 g-xl-8">
        <div class="col-xl-3">
          <!--begin: Statistics Widget 6-->
          <div class="card bg-light-success card-xl-stretch mb-xl-8">
            <!--begin::Body-->
            <div class="card-body my-3">
              <a href="#" class="card-title fw-bold text-success fs-5 mb-3 d-block">{{ dt.home.payment_success }}</a>
              <div class="py-1">
                <span class="text-gray-900 fs-1 fw-bold me-2">{{percentages?.paid_payments_percentage}}%</span>
                <span class="fw-semibold text-muted fs-7">{{ dt.common.average }}</span>
              </div>
              <div class="progress h-7px bg-success bg-opacity-50 mt-7">
                <div class="progress-bar bg-success" role="progressbar" :style="{ width: percentages?.paid_payments_percentage + '%' }" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
            <!--end:: Body-->
          </div>
          <!--end: Statistics Widget 6-->
        </div>

        <div class="col-xl-3">
          <!--begin: Statistics Widget 6-->
          <div class="card bg-light-warning card-xl-stretch mb-xl-8">
            <!--begin::Body-->
            <div class="card-body my-3">
              <a href="#" class="card-title fw-bold text-warning fs-5 mb-3 d-block">{{ dt.home.users_products }}</a>
              <div class="py-1">
                <span class="text-gray-900 fs-1 fw-bold me-2">{{percentages?.users_with_ads_percentage}}%</span>
                <span class="fw-semibold text-muted fs-7">{{ dt.common.goal_48k }}</span>
              </div>
              <div class="progress h-7px bg-warning bg-opacity-50 mt-7">
                <div class="progress-bar bg-warning" role="progressbar" :style="{ width: percentages?.users_with_ads_percentage + '%' }" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
            <!--end:: Body-->
          </div>
          <!--end: Statistics Widget 6-->
        </div>


        <div class="col-xl-3">
          <!--begin: Statistics Widget 6-->
          <div class="card bg-light-info card-xl-stretch mb-xl-8">
            <!--begin::Body-->
            <div class="card-body my-3">
              <a href="#" class="card-title fw-bold text-info fs-5 mb-3 d-block">{{ dt.home.user_sales }}</a>
              <div class="py-1">
                <span class="text-gray-900 fs-1 fw-bold me-2">{{percentages?.users_sold_percentage}}%</span>
                <span class="fw-semibold text-muted fs-7">{{ dt.common.goal_48k }}</span>
              </div>
              <div class="progress h-7px bg-info bg-opacity-50 mt-7">
                <div class="progress-bar bg-info" role="progressbar" :style="{ width: percentages?.users_sold_percentage + '%' }" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
            <!--end:: Body-->
          </div>
          <!--end: Statistics Widget 6-->
        </div>



        <div class="col-xl-3">
          <!--begin: Statistics Widget 6-->
          <div class="card bg-light-primary card-xl-stretch mb-5 mb-xl-8">
            <!--begin::Body-->
            <div class="card-body my-3">
              <a href="#" class="card-title fw-bold text-primary-custom fs-5 mb-3 d-block">{{ dt.home.amount_paid }}</a>
              <div class="py-1">
                <span class="text-gray-900 fs-1 fw-bold me-2">{{percentages?.paid_amount_percentage}}%</span>
                <span class="fw-semibold text-muted fs-7">{{ dt.common.impressions_400k }}</span>
              </div>
              <div class="progress h-7px bg-primary bg-opacity-50 mt-7">
                <div class="progress-bar bg-primary-custom" role="progressbar" :style="{ width: percentages?.paid_amount_percentage + '%' }"  aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
            <!--end:: Body-->
          </div>
          <!--end: Statistics Widget 6-->
        </div>
      </div>

 


      <div class="row g-5 g-xl-8">
        
        <div class="col-xl-3">
          <!--begin::Statistics Widget 5-->
          <a href="#" class="card bg-success hoverable card-xl-stretch mb-xl-8">
            <!--begin::Body-->
            <div class="card-body">
              <i class="ki-duotone ki-briefcase text-white fs-2x ">
                <span class="path1"></span>
                <span class="path2"></span>
              </i>
              <div class="text-white fw-bold fs-2 mb-2 mt-5">{{stats?.paid_amount}} SAR</div>
              <div class="fw-semibold text-white">{{ dt.home.profit_reached }}</div>
            </div>
            <!--end::Body-->
          </a>
          <!--end::Statistics Widget 5-->
        </div>



        <div class="col-xl-3">
          <!--begin::Statistics Widget 5-->
          <a href="#" class="card bg-warning hoverable card-xl-stretch mb-5 mb-xl-8">
            <!--begin::Body-->
            <div class="card-body">
              <i class="ki-duotone ki-chart-pie-simple text-white fs-2x ">
                <span class="path1"></span>
                <span class="path2"></span>
              </i>
              <div class="text-white fw-bold fs-2 mb-2 mt-5">{{stats?.total_ads}} {{ dt.home.products }}</div>
              <div class="fw-semibold text-white">{{ dt.home.products_reached }}</div>
            </div>
            <!--end::Body-->
          </a>
          <!--end::Statistics Widget 5-->
        </div>

        <div class="col-xl-3">
          <!--begin::Statistics Widget 5-->
          <a href="#" class="card bg-info-custom hoverable card-xl-stretch mb-xl-8">
            <!--begin::Body-->
            <div class="card-body">
              <i class="ki-duotone ki-cheque text-gray-100 fs-2x" data-v-b002ad4e=""><span class="path1" data-v-b002ad4e=""></span><span class="path2" data-v-b002ad4e=""></span><span class="path3" data-v-b002ad4e=""></span><span class="path4" data-v-b002ad4e=""></span><span class="path5" data-v-b002ad4e=""></span><span class="path6" data-v-b002ad4e=""></span><span class="path7" data-v-b002ad4e=""></span></i>
              <div class="text-white fw-bold fs-2 mb-2 mt-5">{{stats?.sold_ads}} {{ dt.home.products }}</div>
              <div class="fw-semibold text-white">{{ dt.home.products_paid }}</div>
            </div>
            <!--end::Body-->
          </a>
          <!--end::Statistics Widget 5-->
        </div>

        

        <div class="col-xl-3">
          <!--begin::Statistics Widget 5-->
          <a href="#" class="card bg-primary-custom hoverable card-xl-stretch mb-xl-8">
            <!--begin::Body-->
            <div class="card-body">
              <i class="ki-duotone ki-people text-white fs-2x">
               <span class="path1"></span>
               <span class="path2"></span>
               <span class="path3"></span>
               <span class="path4"></span>
               <span class="path5"></span>
              </i>
              <div class="text-gray-100 fw-bold fs-2 mb-2 mt-5">{{stats?.total_users}} {{ dt.home.user_singular }}</div>
              <div class="fw-semibold text-gray-100">{{ dt.home.new_customers }}</div>
            </div>
            <!--end::Body-->
          </a>
          <!--end::Statistics Widget 5-->
        </div>
      </div>





      <div class="row g-5 g-xl-8">
            <div class="card-toolbar" data-kt-buttons="true">
                <a @click="fetchCharts('year')" 
                   :class="['btn btn-sm btn-color-muted btn-active btn-active-primary px-4 me-1', { active: currentPeriod === 'year' }]"
                >{{ dt.common.year }}</a>

                <a @click="fetchCharts('month')" 
                   :class="['btn btn-sm btn-color-muted btn-active btn-active-primary px-4 me-1', { active: currentPeriod === 'month' }]"
                >{{ dt.common.month }}</a>

                <a @click="fetchCharts('week')" 
                   :class="['btn btn-sm btn-color-muted btn-active btn-active-primary px-4', { active: currentPeriod === 'week' }]"
                >{{ dt.common.week }}</a>
            </div>
        <div class="col-xl-6">
          <!--begin::Charts Widget 2-->

          
          <div class="card card-xl-stretch mb-5 mb-xl-8">
            <!--begin::Header-->
            <div class="card-header border-0 pt-5">
              <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Recent Orders</span>
                <span class="text-muted fw-semibold fs-7">More than {{stats?.sold_ads}} new orders</span>
              </h3>
             
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body">
              <!--begin::Chart-->
              <div id="kt_charts_widget_1_chart" style="height: 350px"></div>
              <!--end::Chart-->
            </div>
            <!--end::Body-->
          </div>
          <!--end::Charts Widget 2-->
        </div>

        <div class="col-xl-6">
          <!--begin::Charts Widget 2-->
          <div class="card card-xl-stretch mb-5 mb-xl-8">
            <!--begin::Header-->
            <div class="card-header border-0 pt-5">
              <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Recent Products</span>
                <span class="text-muted fw-semibold fs-7">More than {{stats?.total_ads}} new orders</span>
              </h3>
             
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body">
              <!--begin::Chart-->
              <div id="kt_charts_widget_2_chart" style="height: 350px"></div>
              <!--end::Chart-->
            </div>
            <!--end::Body-->
          </div>
          <!--end::Charts Widget 2-->
        </div>
      </div>








      <div class="col-xl-12 mb-5 mb-xl-10">
        <!--begin::Table widget 6-->
        <div class="card card-flush h-xl-100">
          <!--begin::Header-->
          <div class="card-header pt-7">
            <!--begin::Title-->
            <h3 class="card-title align-items-start flex-column">
              <span class="card-label fw-bold text-gray-800">KEEPERsport Latest </span>
              <!-- <span class="text-gray-500 mt-1 fw-semibold fs-6">Total 424,567 deliveries</span> -->
            </h3>
           
          </div>
          <!--end::Header-->
          <!--begin::Body-->
          <div class="card-body">
            <!--begin::Nav-->
            <ul class="nav nav-pills nav-pills-custom mb-3" role="tablist">
              <!--begin::Item-->
              <li class="nav-item mb-3 me-3 me-lg-6" role="presentation">
                <!--begin::Link-->
                <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden w-80px h-85px pt-5 pb-2 active" data-bs-toggle="pill" href="#kt_stats_widget_6_tab_1" aria-selected="true" role="tab">
                  <!--begin::Icon-->
                  <div class="nav-icon mb-3">
                    <i class="ki-duotone ki-people fs-1">
                     <span class="path1"></span>
                     <span class="path2"></span>
                     <span class="path3"></span>
                     <span class="path4"></span>
                     <span class="path5"></span>
                    </i>
                  </div>
                  <!--end::Icon-->
                  <!--begin::Title-->
                  <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Users</span>
                  <!--end::Title-->
                  <!--begin::Bullet-->
                  <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                  <!--end::Bullet-->
                </a>
                <!--end::Link-->
              </li>
              <!--end::Item-->
              <!--begin::Item-->
              <li class="nav-item mb-3 me-3 me-lg-6" role="presentation">
                <!--begin::Link-->
                <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden w-80px h-85px pt-5 pb-2" data-bs-toggle="pill" href="#kt_stats_widget_6_tab_2" aria-selected="false" tabindex="-1" role="tab">
                  <!--begin::Icon-->
                  <div class="nav-icon mb-3">
                    <i class="ki-duotone ki-cheque fs-1">
                     <span class="path1"></span>
                     <span class="path2"></span>
                     <span class="path3"></span>
                     <span class="path4"></span>
                     <span class="path5"></span>
                     <span class="path6"></span>
                     <span class="path7"></span>
                    </i>
                  </div>
                  <!--end::Icon-->
                  <!--begin::Title-->
                  <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Orders</span>
                  <!--end::Title-->
                  <!--begin::Bullet-->
                  <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                  <!--end::Bullet-->
                </a>
                <!--end::Link-->
              </li>
              <!--end::Item-->
              <!--begin::Item-->
              <li class="nav-item mb-3 me-3 me-lg-6" role="presentation">
                <!--begin::Link-->
                <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden w-80px h-85px pt-5 pb-2" data-bs-toggle="pill" href="#kt_stats_widget_6_tab_3" aria-selected="false" tabindex="-1" role="tab">
                  <!--begin::Icon-->
                  <div class="nav-icon mb-3">
                    <i class="ki-duotone ki-logistic fs-1">
                      <span class="path1"></span>
                      <span class="path2"></span>
                      <span class="path3"></span>
                      <span class="path4"></span>
                      <span class="path5"></span>
                      <span class="path6"></span>
                      <span class="path7"></span>
                    </i>
                  </div>
                  <!--end::Icon-->
                  <!--begin::Title-->
                  <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Products</span>
                  <!--end::Title-->
                  <!--begin::Bullet-->
                  <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                  <!--end::Bullet-->
                </a>
                <!--end::Link-->
              </li>
              <!--end::Item-->
            </ul>
            <!--end::Nav-->
            <!--begin::Tab Content-->
            <div class="tab-content">
              <!--begin::Tap pane-->
              <div class="tab-pane fade active show" id="kt_stats_widget_6_tab_1" role="tabpanel">
                <!--begin::Table container-->
                <div class="table-responsive">
                  <!--begin::Table-->
                  <table class="table table-row-dashed align-middle gs-0 gy-4 my-0">
                    <!--begin::Table head-->
                    <thead>
                      <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                        <th class="p-0 w-200px w-xxl-450px"></th>
                        <th class="p-0 min-w-150px"></th>
                        <th class="p-0 min-w-150px"></th>
                        <th class="p-0 min-w-190px"></th>
                        <th class="p-0 w-50px"></th>
                      </tr>
                    </thead>
                    <!--end::Table head-->
                    <!--begin::Table body-->
                    <tbody>
                      <tr v-for="(user,indexUser) in users">
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px me-3">
                              <img :src="user.logo" class="" alt="">
                            </div>
                            <div class="d-flex justify-content-start flex-column">
                              <a href="#" class="text-gray-900 fw-bold text-hover-primary mb-1 fs-6">{{user.name}}</a>
                              <span class="text-muted fw-semibold d-block fs-7">{{user.mobile}}</span>
                            </div>
                          </div>
                        </td>
                        <td>
                          <span class="text-gray-800 fw-bold d-block mb-1 fs-6">{{user?.country?.name}}</span>
                          <span class="fw-semibold text-gray-500 d-block">{{user?.city?.name}}</span>
                        </td>
                        <td>
                          <a href="#" class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{user.account_age}}</a>
                          <div v-if="user.verification==1" class="badge badge-light-primary fw-bold">verified</div>
                          <div v-else class="badge badge-light-secondary fw-bold">un verified</div>                        </td> 
                        <td>
                        <div class="rating">
                          <div 
                            v-for="i in 5" 
                            :key="i" 
                            class="rating-label" 
                            :class="{ checked: i <= user.average_rating }"
                          >
                            <i class="ki-solid ki-star fs-6"></i>
                          </div>
                        </div>
                          <span class="text-muted fw-semibold d-block fs-7 mt-1">Rating</span>
                        </td>
                        <td class="text-end">
                          <a @click="UserOpen(user)" href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                            <i class="ki-duotone ki-black-right fs-2 text-gray-500"></i>
                          </a>
                        </td>
                      </tr>
                   
                    </tbody>
                    <!--end::Table body-->
                  </table>
                </div>
                <!--end::Table-->
              </div>
              <!--end::Tap pane-->
              <!--begin::Tap pane-->
              <div class="tab-pane fade" id="kt_stats_widget_6_tab_2" role="tabpanel">
                <!--begin::Table container-->
                <div class="table-responsive">
                  <!--begin::Table-->
                  <table class="table table-row-dashed align-middle gs-0 gy-4 my-0">
                    <!--begin::Table head-->
                    <thead>
                      <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                        <th class="p-0 w-200px w-xxl-450px"></th>
                        <th class="p-0 min-w-150px"></th>
                        <th class="p-0 min-w-150px"></th>
                        <th class="p-0 min-w-190px"></th>
                        <th class="p-0 w-50px"></th>
                      </tr>
                    </thead>
                    <!--end::Table head-->
                    <!--begin::Table body-->
                    <tbody>
                      <tr v-for="(pa,indexpa) in payments" key="pa.id"  >
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px me-3">
                              <img :src="pa?.user?.logo" class="" alt="">
                            </div>
                            <div class="d-flex justify-content-start flex-column">
                              <a href="#" class="text-gray-900 fw-bold text-hover-primary mb-1 fs-6">{{pa?.user?.name}}</a>
                              <span class="text-muted fw-semibold d-block fs-7">{{pa?.user?.mobile}}</span>
                            </div>
                          </div>
                        </td>
                        <td>
                          <span class="text-gray-800 fw-bold d-block mb-1 fs-6">{{pa.track_id}}</span>
                          <span class="badge badge-light-success fs-base" v-if="pa.status == 'paid'">{{pa.status}}</span>
                          <span class="badge badge-light-warning fs-base" v-else-if="pa.status == 'pending'">{{pa.status}}</span>
                          <span class="badge badge-light-warning fs-base" v-else>{{pa.status}}</span>
                          
                        </td>
                        <td>
                          <a href="#" class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{pa.amount}} SAR</a>
                          <span class="text-muted fw-semibold d-block fs-7">amount</span>
                        </td>


                         <td>
                          <a href="#" class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{pa.created_at}}</a>
                          <span class="text-muted fw-semibold d-block fs-7">Date</span>
                        </td>


                        
                        <td class="text-end">
                          <a @click="PaymentOpen(pa)" href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                            <i class="ki-duotone ki-black-right fs-2 text-gray-500"></i>
                          </a>
                        </td> 
                      </tr>
                    
                    </tbody>
                    <!--end::Table body-->
                  </table>
                </div>
                <!--end::Table-->
              </div>
              <!--end::Tap pane-->
              <!--begin::Tap pane-->
              <div class="tab-pane fade" id="kt_stats_widget_6_tab_3" role="tabpanel">
                <!--begin::Table container-->
                <div class="table-responsive">
                  <!--begin::Table-->
                  <table class="table table-row-dashed align-middle gs-0 gy-4 my-0">
                    <!--begin::Table head-->
                    <thead>
                      <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                        <th class="p-0 w-200px w-xxl-450px"></th>
                        <th class="p-0 min-w-150px"></th>
                        <th class="p-0 min-w-150px"></th>
                        <th class="p-0 min-w-190px"></th>
                        <th class="p-0 w-50px"></th>
                      </tr>
                    </thead>
                    <!--end::Table head-->
                    <!--begin::Table body-->
                    <tbody>
                      <tr v-for="(ad,indexAd) in ads">
                        <td width="40%">
                          <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px me-3">
                              <img :src="ad?.image_basic?.url" class="" alt="">
                            </div>
                            <div class="d-flex justify-content-start flex-column">
                              <a href="#" class="text-gray-900 fw-bold text-hover-primary mb-1 fs-6">{{ad?.title}}</a>
                              <span class="text-muted fw-semibold d-block fs-7">{{ad?.user?.name}}</span>
                            </div>
                          </div>
                        </td>
                        <td>
                          <span class="text-gray-800 fw-bold d-block mb-1 fs-6">{{ad?.brand?.name}}</span>
                          <span class="fw-semibold text-gray-500 d-block">{{ad?.category?.name}}</span>
                        </td>
                        <td>
                          <span class="text-gray-800 fw-bold d-block mb-1 fs-6">{{ad?.price}} SAR</span>
                          <span class="fw-semibold text-gray-500 d-block">{{ad?.product_type}}</span>
                        </td>


                        <td width="20%">
                          <span class="text-gray-800 fw-bold d-block mb-1 fs-6">{{ad?.updated_at_human}}</span>
                          <span class="fw-semibold text-gray-500 d-block">views : {{ad?.views}}</span>
                        </td>


                    
                        <td class="text-end">
                          <a @click="AdOpen(ad)" href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                            <i class="ki-duotone ki-black-right fs-2 text-gray-500"></i>
                          </a>
                        </td>

                      </tr>
                      
                    </tbody>
                    <!--end::Table body-->
                  </table>
                </div>
                <!--end::Table-->
              </div>
              <!--end::Tap pane-->
            </div>
            <!--end::Tab Content-->
          </div>
          <!--end: Card Body-->
        </div>
        <!--end::Table widget 6-->
      </div>



  </div>


</template>

<script>
import axios from 'axios';
import { mapState } from 'vuex';


 
export default {
  components: {
  },
    data() {
        return {
            languages:{},
            isLoading: false,
         
            URLPercentage:'Dashboard/getAllItemsPercenatge',
            URLChartsData:'Dashboard/getChartsData',


            percentages:null,
            stats:null,
            users:null,
            ads:null,
            payments:null,
            currentPeriod:'month',
            chartWidget1: null,
            chartWidget2: null,
        };
    }, 

    computed: {

        locale() {
            return this.$route.params.locale;
        },
  

    },
 

    mounted() {
       
        this.fetchItems();

         this.$nextTick(() => {
            this.fetchCharts('month');
          });    
    },

   
     activated() { 
        this.$nextTick(() => {
          this.fetchCharts('month');
        });
      },
 
    methods: { 


      async fetchCharts(period) {

        this.currentPeriod = period
      
         await axios.get(this.URLChartsData, {
              params: {
                period: period,  
              }
            })
              .then(response => {
                  let data = response.data.charts
                  this.initChartsWidget1(data.sales_chart);
                  this.initChartsWidget2(data.ads_chart);
                  this.isLoading = false;
              })
              .catch(error => {
                 Swal.fire({
                    text: error,
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                  });

                 this.isLoading = false;
              });
      },




       initChartsWidget1(chartData) {
          const element = document.getElementById("kt_charts_widget_1_chart");
          if (!element) return;

          const height = parseInt(KTUtil.css(element, 'height'));
          const baseColor = KTUtil.getCssVariableValue('--bs-warning') || "#FFC107";
          const secondaryColor = "#E4E6EF"; // لون ثابت

          // تدمير الرسم السابق إذا كان موجود
          if (this.chartWidget1) {
              this.chartWidget1.destroy();
          }

          const options = {
              series: chartData.series,
              chart: { type: 'bar', height: height, toolbar: { show: true } },
              plotOptions: { bar: { horizontal: false, columnWidth: '30%', borderRadius: 6 } },
              colors: [baseColor, secondaryColor],
              xaxis: { categories: chartData.categories },
              dataLabels: {
                  enabled: false  // إخفاء الأرقام
              }
          };

          // تخزين الرسم الجديد في متغير ليمكن تدميره لاحقاً
          this.chartWidget1 = new ApexCharts(element, options);
          this.chartWidget1.render();
      },

      

      initChartsWidget2(chartData) {
        const element = document.getElementById("kt_charts_widget_2_chart");
        if (!element) return;

        const height = parseInt(KTUtil.css(element, 'height'));
        const baseColor = KTUtil.getCssVariableValue('--bs-primary') || "#0d6efd";
        const secondaryColor = "#E4E6EF";

        // تدمير الرسم السابق إذا كان موجود
        if (this.chartWidget2) {
            this.chartWidget2.destroy();
        }

        const options = {
            series: chartData.series,
            chart: { type: 'bar', height: height, toolbar: { show: true } },
            plotOptions: { bar: { horizontal: false, columnWidth: '30%', borderRadius: 6 } },
            colors: [baseColor, secondaryColor],
            xaxis: { categories: chartData.categories },
            dataLabels: {
                enabled: false  // إخفاء الأرقام
            }
        };

        // تخزين الرسم الجديد في متغير ليمكن تدميره لاحقاً
        this.chartWidget2 = new ApexCharts(element, options);
        this.chartWidget2.render();
    },

      


      async fetchItems(page,query = '') {
        this.currentPage = page;
        this.isLoading = true;
          await axios.get(this.URLPercentage, {
              params: {
                search: this.searchQuery,  // تمرير قيمة البحث
              }
            })
              .then(response => {
                  this.percentages = response.data.items.percentages;
                  this.stats = response.data.items.stats;
                  this.users = response.data.items.latest.users;
                  this.ads = response.data.items.latest.ads;
                  this.payments = response.data.items.latest.payments;
                  this.isLoading = false;
              })
              .catch(error => {
                 Swal.fire({
              text: error,
              icon: "error",
              buttonsStyling: false,
              confirmButtonText: "Ok, got it!",
              customClass: {
                  confirmButton: "btn btn-primary"
              }
          });

                 this.isLoading = false;
              });
      },


       

      AdOpen(ad) {
        this.$router.push({
          path: `/${this.$route.params.locale}/dashboard/ads/ad`,
          query: { openModal: true, adId: ad.id }
        });
      },




      UserOpen(user) {
        this.$router.push({
          path: `/${this.$route.params.locale}/dashboard/user_management/users`,
          query: { openModal: true, userId: user.id }
        });
      },

      PaymentOpen(pa) {
        this.$router.push({
          path: `/${this.$route.params.locale}/dashboard/orders/order`,
          query: { openModal: true, orderId: pa.id }
        });
      },






  
  }

 
};
</script>




<style type="text/css" scoped>
  
  .bg-info-custom
  {
    --bs-bg-rgb-color: #7239ea !important;
    background-color: #7239ea !important;
  }


  .bg-primary-custom
  {
    --bs-bg-rgb-color: #1b84ff !important;
    background-color: #1b84ff !important;
  } 


  .text-primary-custom
  {
    color: #1b84ff !important;
  }

  .text-info {
    --bs-text-opacity: 1;
    color: rgba(var(--bs-info-rgb), var(--bs-text-opacity)) !important;}


    .rating-label {
      padding: 0px;
      background: inherit;
    }

    :root {
  --bs-gray-300: #E4E6EF !important; /* نفس قيمة Metronic */
}


</style>

