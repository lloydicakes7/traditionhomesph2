@if ($faqs->isNotEmpty())
    <section class="py-5 bg-light" id="faq">
        <div class="container">
            <div class="text-center mb-4">
                <h2 class="h3 mb-1">
                    <i class="fas fa-fw fa-question-circle text-primary mr-1"></i>Frequently Asked Questions
                </h2>
                <p class="text-muted mb-0">Quick answers to what homeowners ask most.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-9">
                    <x-adminlte-accordion id="publicFaq">
                        @foreach ($faqs as $faq)
                            <x-adminlte-accordion-item :title="$faq->question" accordion-id="publicFaq"
                                :show="$loop->first">
                                <div class="mb-0" style="line-height: 1.7;">{!! $faq->answer_html !!}</div>
                            </x-adminlte-accordion-item>
                        @endforeach
                    </x-adminlte-accordion>
                </div>
            </div>
        </div>
    </section>
@endif
