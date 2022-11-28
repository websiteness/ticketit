@push('header_styles')

@endpush
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="counter">
            <div class="counter__box">
                <div class="counter__top">
                    <h3 class="counter__value">{{$statuses_count['Overdue']}}</h3>
                    <div class="counter__icon">
                        <img src="{{asset('images/ticket-system/new/overdue.png')}}" alt="">
                    </div><!-- .counter__icon -->
                </div><!-- .counter__top -->
                <div class="counter__bottom">
                    <h4 class="counter__title">Overdue</h4>
                </div><!-- .counter__bottom -->
            </div><!-- .counter__box -->
            <div class="counter__box">
                <div class="counter__top">
                    <h3 class="counter__value">{{$statuses_count['No Response']}}</h3>
                    <div class="counter__icon">
                        <img src="{{asset('images/ticket-system/new/no-response.png')}}" alt="">
                    </div><!-- .counter__icon -->
                </div><!-- .counter__top -->
                <div class="counter__bottom">
                    <h4 class="counter__title">No Response</h4>
                </div><!-- .counter__bottom -->
            </div><!-- .counter__box -->
            <div class="counter__box">
                <div class="counter__top">
                    <h3 class="counter__value">{{$statuses_count['Waiting on Support']}}</h3>
                    <div class="counter__icon">
                        <img src="{{asset('images/ticket-system/new/waiting-on-support.png')}}" alt="">
                    </div><!-- .counter__icon -->
                </div><!-- .counter__top -->
                <div class="counter__bottom">
                    <h4 class="counter__title">Waiting On Support</h4>
                </div><!-- .counter__bottom -->
            </div><!-- .counter__box -->
            <div class="counter__box">
                <div class="counter__top">
                    <h3 class="counter__value">{{$statuses_count['Need Feedback']}}</h3>
                    <div class="counter__icon">
                        <img src="{{asset('images/ticket-system/new/need-feedback.png')}}" alt="">
                    </div><!-- .counter__icon -->
                </div><!-- .counter__top -->
                <div class="counter__bottom">
                    <h4 class="counter__title">Need Feedback</h4>
                </div><!-- .counter__bottom -->
            </div><!-- .counter__box -->
            <div class="counter__box">
                <div class="counter__top">
                    <h3 class="counter__value">{{$statuses_count['In Progress']}}</h3>
                    <div class="counter__icon">
                        <img src="{{asset('images/ticket-system/new/in-progress.png')}}" alt="">
                    </div><!-- .counter__icon -->
                </div><!-- .counter__top -->
                <div class="counter__bottom">
                    <h4 class="counter__title">In Progress</h4>
                </div><!-- .counter__bottom -->
            </div><!-- .counter__box -->
        </div><!-- .counter -->
    </div><!-- .col-md-12 col-sm-12 col-xs-12 -->
</div><!-- .row -->
                                                    
@push('footer_scripts')

@endpush