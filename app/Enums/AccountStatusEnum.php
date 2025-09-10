<?php

namespace App\Enums;

enum AccountStatusEnum: string {

    case Active = 'active';
    case Draft = 'draft';
    case Expired = 'expired';
    case Cancelled = 'cancelled';


    public function isActive( AccountStatusEnum $status = null ): bool
    {
        $status ??= $this;

        return match ( $status ) {
            AccountStatusEnum::Cancelled,
            AccountStatusEnum::Expired => false,

            default => true,
        };
    }

}