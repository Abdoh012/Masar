"use client"

import * as React from "react"
import * as SelectPrimitive from "radix-ui/select"
import { CheckIcon, ChevronDownIcon, ChevronUpIcon } from "lucide-react"

import { cn } from "@/shared/lib/utils"

const Select = SelectPrimitive.Select

const SelectGroup = SelectPrimitive.SelectGroup

const SelectValue = SelectPrimitive.SelectValue

const SelectTrigger = React.forwardRef<
  React.ElementRef<typeof SelectPrimitive.SelectTrigger>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.SelectTrigger>
>(({ className, children, ...props }, ref) => (
  <SelectPrimitive.SelectTrigger
    ref={ref}
    className={cn(
      "flex h-10 w-full items-center justify-between gap-2 rounded-md border border-input bg-card px-3 py-2 text-sm text-foreground shadow-xs outline-none transition-[border-color,box-shadow] duration-200 ring-2 ring-transparent focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 data-[placeholder]:text-muted-foreground [&>span]:line-clamp-1",
      className,
    )}
    {...props}
  >
    {children}
    <SelectPrimitive.SelectIcon asChild>
      <ChevronDownIcon className="size-4 shrink-0 text-muted-foreground" />
    </SelectPrimitive.SelectIcon>
  </SelectPrimitive.SelectTrigger>
))
SelectTrigger.displayName = SelectPrimitive.SelectTrigger.displayName

const SelectContent = React.forwardRef<
  React.ElementRef<typeof SelectPrimitive.SelectContent>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.SelectContent>
>(({ className, children, position = "popper", ...props }, ref) => (
  <SelectPrimitive.SelectPortal>
    <SelectPrimitive.SelectContent
      ref={ref}
      position={position}
      className={cn(
        "relative z-50 max-h-96 min-w-[8rem] overflow-hidden rounded-md border border-border bg-card text-foreground shadow-lg",
        position === "popper" && "data-[side=bottom]:translate-y-1 data-[side=top]:-translate-y-1",
        className,
      )}
      {...props}
    >
      <SelectPrimitive.SelectScrollUpButton className="flex h-6 cursor-default items-center justify-center bg-card">
        <ChevronUpIcon className="size-4 text-muted-foreground" />
      </SelectPrimitive.SelectScrollUpButton>
      <SelectPrimitive.SelectViewport
        className={cn(
          "p-1",
          position === "popper" &&
            "h-[var(--radix-select-trigger-height)] w-full min-w-[var(--radix-select-trigger-width)]",
        )}
      >
        {children}
      </SelectPrimitive.SelectViewport>
      <SelectPrimitive.SelectScrollDownButton className="flex h-6 cursor-default items-center justify-center bg-card">
        <ChevronDownIcon className="size-4 text-muted-foreground" />
      </SelectPrimitive.SelectScrollDownButton>
    </SelectPrimitive.SelectContent>
  </SelectPrimitive.SelectPortal>
))
SelectContent.displayName = SelectPrimitive.SelectContent.displayName

const SelectLabel = React.forwardRef<
  React.ElementRef<typeof SelectPrimitive.SelectLabel>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.SelectLabel>
>(({ className, ...props }, ref) => (
  <SelectPrimitive.SelectLabel
    ref={ref}
    className={cn("px-2 py-1.5 text-sm font-medium", className)}
    {...props}
  />
))
SelectLabel.displayName = SelectPrimitive.SelectLabel.displayName

const SelectItem = React.forwardRef<
  React.ElementRef<typeof SelectPrimitive.SelectItem>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.SelectItem>
>(({ className, children, ...props }, ref) => (
  <SelectPrimitive.SelectItem
    ref={ref}
    className={cn(
      "relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pr-8 pl-2 text-sm outline-none focus:bg-primary-tint focus:text-primary-text data-[disabled]:pointer-events-none data-[disabled]:opacity-50",
      className,
    )}
    {...props}
  >
    <span className="absolute right-2 flex size-3.5 items-center justify-center">
      <SelectPrimitive.SelectItemIndicator>
        <CheckIcon className="size-4" />
      </SelectPrimitive.SelectItemIndicator>
    </span>
    <SelectPrimitive.SelectItemText>{children}</SelectPrimitive.SelectItemText>
  </SelectPrimitive.SelectItem>
))
SelectItem.displayName = SelectPrimitive.SelectItem.displayName

const SelectSeparator = React.forwardRef<
  React.ElementRef<typeof SelectPrimitive.SelectSeparator>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.SelectSeparator>
>(({ className, ...props }, ref) => (
  <SelectPrimitive.SelectSeparator
    ref={ref}
    className={cn("-mx-1 my-1 h-px bg-border", className)}
    {...props}
  />
))
SelectSeparator.displayName = SelectPrimitive.SelectSeparator.displayName

export {
  Select,
  SelectGroup,
  SelectValue,
  SelectTrigger,
  SelectContent,
  SelectLabel,
  SelectItem,
  SelectSeparator,
}