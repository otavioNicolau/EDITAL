import * as React from "react"
import { cn } from "@/lib/utils"

type BadgeVariant = "default" | "secondary" | "destructive" | "outline"

export interface BadgeProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: BadgeVariant
}

function getBadgeClasses(variant: BadgeVariant = "default") {
  const baseClasses = "badge d-inline-flex align-items-center"
  
  const variantClasses = {
    default: "bg-primary text-white",
    secondary: "bg-secondary text-white",
    destructive: "bg-danger text-white",
    outline: "border border-primary text-primary bg-transparent",
  }
  
  return `${baseClasses} ${variantClasses[variant]}`
}

function Badge({ className, variant = "default", ...props }: BadgeProps) {
  return (
    <div className={cn(getBadgeClasses(variant), className)} {...props} />
  )
}

export { Badge }
